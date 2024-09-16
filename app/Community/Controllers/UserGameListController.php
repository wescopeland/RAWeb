<?php

declare(strict_types=1);

namespace App\Community\Controllers;

use App\Community\Data\UserGameListPagePropsData;
use App\Community\Enums\UserGameListType;
use App\Http\Controller;
use App\Models\UserGameListEntry;
use Illuminate\Http\Request;
use App\Models\User;
use App\Platform\Actions\BuildGameListAction;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Platform\Data\SystemData;
use App\Platform\Enums\GameListType;

class UserGameListController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        // TODO authorize
        // TODO redirect
        // TODO want to dev games, separate controller?
        // TODO request object validation
        // TODO prefetching on pagination hover too often
        // TODO remember user's previous state somehow
        // TODO tests
        // TODO invalidate cache on sort, remove, etc
        // TODO N+1 problem for leaderboard counts
        // TODO remember the user's state settings somehow, Laravel session + redis ?
        // TODO reset table to default button
        // TODO test light mode
        // TODO doing the same sort over and over causes the rows to change order
        // TODO filter by tags
        // TODO rename "Want to Play Games" to "Backlog"
        // TODO long loading
        // TODO two fetches on every sort
        // TODO white space before game title labels when all columns enabled

        /** @var User $user */
        $user = $request->user();

        $paginatedData = (new BuildGameListAction())->execute(
            GameListType::UserPlay,
            user: $user,
            page: $request->input('page', 1),
        );

        // Only allow filtering by systems the user has games on their list for.
        $filterableSystemOptions = $user->gameListEntries(UserGameListType::Play)
            ->with('game.system')
            ->get()
            ->pluck('game.system')
            ->unique('id')
            ->map(fn($system) => SystemData::fromSystem($system))
            ->values()
            ->all();
                        
        $props = new UserGameListPagePropsData(
            paginatedGameListEntries: $paginatedData,
            filterableSystemOptions: $filterableSystemOptions,
        );

        return Inertia::render('game-list/play', $props);
    }

    public function create(): void
    {
    }

    public function store(Request $request): void
    {
    }

    public function show(UserGameListEntry $userGameListEntry): void
    {
    }

    public function edit(UserGameListEntry $userGameListEntry): void
    {
    }

    public function update(Request $request, UserGameListEntry $userGameListEntry): void
    {
    }

    public function destroy(UserGameListEntry $userGameListEntry): void
    {
    }
}
