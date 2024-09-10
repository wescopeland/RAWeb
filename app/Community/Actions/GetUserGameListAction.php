<?php

namespace App\Community\Actions;

use App\Community\Data\UserGameListEntryData;
use App\Community\Enums\TicketState;
use App\Community\Enums\UserGameListType;
use App\Data\PaginatedData;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GetUserGameListAction
{
    public function execute(
        User $user,
        int $perPage = 25,
        int $page = 1,
        array $sort = [],
        array $filters = [],
    ): PaginatedData {
        // TODO make the DTO fields more generic
        $query = $user->gameListEntries(UserGameListType::Play)
            ->getQuery()
            ->join('GameData', 'GameData.ID', '=', 'SetRequest.GameID')
            ->join('Console', 'Console.ID', '=', 'GameData.ConsoleID')
            ->with([
                'game.system',
                'game.leaderboards', // TODO this shouldn't be needed
                'game.visibleLeaderboards',
                'game.tickets' => function ($query) {
                    $query->whereIn('ReportState', [TicketState::Open, TicketState::Request])
                          ->whereNull('Ticket.deleted_at');
                },
                'game.playerGames' => function ($query) use ($user) {
                    $query->forUser($user);
                },
                'game.lastAchievementUpdate',
            ])
            ->select('SetRequest.*');

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $sort);

        $entries = $query->paginate($perPage, ['*'], 'page', $page);

        $transformedEntries = $entries->map(function ($entry) {

            return UserGameListEntryData::from($entry)->include(
                'game.badgeUrl',
                'game.system.nameShort',
                'game.system.iconUrl',
                'game.achievementsPublished',
                'game.pointsTotal',
                'game.pointsWeighted',
                'game.lastUpdated',
                'game.numLeaderboardsVisible',
                'game.numUnresolvedTickets',
                'game.releasedAt',
                'game.releasedAtGranularity',
                'playerGame',
            );
        });

        $paginator = new LengthAwarePaginator(
            items: $transformedEntries,
            total: $entries->total(),
            perPage: $entries->perPage(),
            currentPage: $entries->currentPage(),
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return PaginatedData::fromLengthAwarePaginator($paginator);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $filterKey => $filterValues) {
            switch ($filterKey) {
                case 'system':
                    $query->whereIn('Console.ID', $filterValues);
                    break;
    
                default:
                    break;
            }
        }
    }

    protected function applySorting(Builder $query, array $sort): void
    {
        $validSortFields = [
            'title',
            'system',
            'achievementsPublished',
            'pointsTotal',
            'retroRatio',
            'lastUpdated',
            'releasedAt',
            'numLeaderboardsVisible',
            'numUnresolvedTickets',
        ];

        if (isset($sort['field']) && in_array($sort['field'], $validSortFields)) {
            $sortDirection = $sort['direction'] ?? 'asc';

            switch ($sort['field']) {
                case 'title':
                    $this->applyGameTitleSorting($query, $sortDirection);
                    break;

                case 'system':
                    $query->orderBy('Console.name_short', $sortDirection);
                    break;

                case 'achievementsPublished':
                    $query->orderBy('GameData.achievements_published', $sortDirection);

                case 'pointsTotal':
                    $query->orderBy('GameData.points_total', $sortDirection);
                    break;

                case 'retroRatio':
                    $query->selectRaw(
                        "CASE 
                            WHEN GameData.points_total = 0 THEN 0
                            ELSE GameData.TotalTruePoints / GameData.points_total
                        END AS retro_ratio"
                    )
                        ->orderBy('retro_ratio', $sortDirection);
                    break;

                case 'lastUpdated':
                    $query->selectRaw(
                        "COALESCE(
                            (SELECT MAX(DateModified) FROM Achievements WHERE Achievements.GameID = GameData.ID),
                            GameData.Updated
                        ) AS last_updated"
                    )
                    ->orderBy('last_updated', $sortDirection);
                    break;

                case 'releasedAt':
                    $this->applyReleasedAtSorting($query, $sortDirection);
                    break;

                case 'numLeaderboardsVisible':
                    $query->leftJoin('LeaderboardDef', function ($join) {
                        $join->on('LeaderboardDef.GameID', '=', 'GameData.ID')
                             ->where('LeaderboardDef.DisplayOrder', '>=', 0)
                             ->whereNull('LeaderboardDef.deleted_at');
                    })
                    ->groupBy('SetRequest.ID', 'SetRequest.GameID', 'GameData.Title')
                    ->select('SetRequest.*', 'GameData.Title')
                    ->selectRaw('COUNT(DISTINCT LeaderboardDef.ID) as num_leaderboards_visible')
                    ->orderBy('num_leaderboards_visible', $sortDirection);
                    break;

                case 'numUnresolvedTickets':
                        $ticketSubquery = DB::table('Achievements')
                            ->join('Ticket', 'Ticket.AchievementID', '=', 'Achievements.ID')
                            ->whereIn('Ticket.ReportState', [
                                TicketState::Open,
                                TicketState::Request
                            ])
                            ->whereNull('Ticket.deleted_at')
                            ->groupBy('Achievements.GameID')
                            ->select('Achievements.GameID', DB::raw('COUNT(DISTINCT Ticket.ID) as unresolved_ticket_count'));
    
                        $query->leftJoinSub($ticketSubquery, 'ticket_counts', function ($join) {
                            $join->on('GameData.ID', '=', 'ticket_counts.GameID');
                        })
                        ->select('SetRequest.*', 'GameData.Title')
                        ->addSelect(DB::raw('COALESCE(ticket_counts.unresolved_ticket_count, 0) as num_unresolved_tickets'))
                        ->orderBy('num_unresolved_tickets', $sortDirection);
                        break;

                default:
                    $this->applyGameTitleSorting($query, $sortDirection);
            }
        } else {
            $this->applyGameTitleSorting($query);
        }
    }

    /**
     * Ensure games on the list are sorted properly.
     * For titles starting with "~", the sort order is determined by the content
     * within the "~" markers followed by the content after the "~". This ensures
     * that titles with "~" are grouped together and sorted alphabetically based
     * on their designated categories and then by their actual game title.
     *
     * The "~" prefix is retained in the SortTitle of games with "~" to ensure these
     * games are sorted at the end of the list, maintaining a clear separation from
     * non-prefixed titles. This approach allows game titles to be grouped and sorted
     * in a specific order:
     *
     * 1. Non-prefixed titles are sorted alphabetically at the beginning of the list.
     * 2. Titles prefixed with "~" are grouped at the end, sorted first by the category
     *    specified within the "~" markers, and then alphabetically by the title following
     *    the "~".
     */
    protected function applyGameTitleSorting(Builder $query, string $sortDirection = 'asc'): void
    {
        $query->selectRaw(
            "GameData.*, 
            CASE 
                WHEN GameData.Title LIKE '~%' THEN 1
                ELSE 0
            END AS SortPrefix,
            CASE 
                WHEN GameData.Title LIKE '~%' THEN CONCAT('~', SUBSTRING_INDEX(SUBSTRING(GameData.Title, 2), '~', 1), ' ', TRIM(SUBSTRING(GameData.Title, LOCATE('~', GameData.Title, 2) + 1)))
                ELSE GameData.Title
            END AS SortTitle"
        )
        ->orderByRaw('SortPrefix ' . $sortDirection) // Sort non-prefixed titles first.
        ->orderByRaw('SortTitle ' . $sortDirection); // Then sort alphabetically by SortTitle.  
    }

    protected function applyReleasedAtSorting(Builder $query, string $sortDirection = 'asc'): void
    {
        $query->selectRaw(
            "GameData.*, 
            CASE 
                WHEN GameData.released_at_granularity = 'year' THEN DATE_FORMAT(GameData.released_at, '%Y-01-01')
                WHEN GameData.released_at_granularity = 'month' THEN DATE_FORMAT(GameData.released_at, '%Y-%m-01')
                ELSE GameData.released_at
            END AS normalized_released_at"
        )
        ->orderBy('normalized_released_at', $sortDirection);
    }
}