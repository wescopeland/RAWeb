<?php

use App\Enums\Permissions;
use App\Models\Achievement;
use App\Models\EventAchievement;
use App\Models\Game;
use App\Models\GameRecentPlayer;
use App\Models\PlayerGame;
use App\Models\User;
use App\Platform\Enums\AchievementFlag;
use App\Platform\Services\GameTopAchieversService;
use Illuminate\Database\Eloquent\Collection;

function getGameRankAndScore(int $gameID, User $user): array
{
    if (empty($gameID)) {
        return [];
    }

    $dateClause = greatestStatement(['pg.last_unlock_hardcore_at', 'pg.last_unlock_at']);
    $rankClause = "ROW_NUMBER() OVER (ORDER BY pg.Points DESC, $dateClause ASC) UserRank";
    $untrackedClause = "AND ua.Untracked = 0";
    if ($user->Untracked) {
        $rankClause = "NULL AS UserRank";
        $untrackedClause = "";
    }

    $query = "WITH data
    AS (SELECT ua.User, ua.ulid AS ULID, $rankClause, pg.Points AS TotalScore, $dateClause AS LastAward
        FROM player_games AS pg
        INNER JOIN UserAccounts AS ua ON ua.ID = pg.user_id
        WHERE pg.game_id = $gameID $untrackedClause
        GROUP BY ua.User
        ORDER BY TotalScore DESC, LastAward ASC
   ) SELECT * FROM data WHERE User = :username";

    return legacyDbFetchAll($query, ['username' => $user->User])->toArray();
}

function getUserProgress(User $user, array $gameIDs, int $numRecentAchievements = -1, bool $withGameInfo = false): array
{
    $libraryOut = [];

    $awardedData = [];
    $gameInfo = [];
    $unlockedAchievements = [];
    $lockedAchievements = [];

    $games = Game::with('system')->whereIn('ID', $gameIDs)->get()->keyBy('ID');
    $playerGames = PlayerGame::where('user_id', '=', $user->ID)
        ->whereIn('game_id', $gameIDs)
        ->get()
        ->keyBy('game_id');

    foreach ($gameIDs as $gameID) {
        $game = $games->get($gameID);
        if (!$game) {
            $awardedData[$gameID] = [
                'NumPossibleAchievements' => 0,
                'PossibleScore' => 0,
                'NumAchieved' => 0,
                'ScoreAchieved' => 0,
                'NumAchievedHardcore' => 0,
                'ScoreAchievedHardcore' => 0,
            ];
            continue;
        }

        $playerGame = $playerGames->get($gameID);

        $awardedData[$gameID] = [
            'NumPossibleAchievements' => $game->achievements_published ?? 0,
            'PossibleScore' => $game->points_total ?? 0,
            'NumAchieved' => $playerGame ? ($playerGame->achievements_unlocked ?? 0) : 0,
            'ScoreAchieved' => $playerGame ? ($playerGame->points ?? 0) : 0,
            'NumAchievedHardcore' => $playerGame ? ($playerGame->achievements_unlocked_hardcore ?? 0) : 0,
            'ScoreAchievedHardcore' => $playerGame ? ($playerGame->points_hardcore ?? 0) : 0,
        ];

        if ($withGameInfo) {
            $gameInfo[$gameID] = [
                'ID' => $game->ID,
                'Title' => $game->Title,
                'ConsoleID' => (int) $game->system->ID,
                'ConsoleName' => $game->system->Name,
                'ForumTopicID' => (int) $game->ForumTopicID,
                'Flags' => (int) $game->Flags,
                'ImageIcon' => $game->ImageIcon,
                'ImageTitle' => $game->ImageTitle,
                'ImageIngame' => $game->ImageIngame,
                'ImageBoxArt' => $game->ImageBoxArt,
                'Publisher' => $game->Publisher,
                'Developer' => $game->Developer,
                'Genre' => $game->Genre,
                'Released' => $game->released_at?->format('Y-m-d'),
                'ReleasedAtGranularity' => $game->released_at_granularity,
            ];
        }
    }

    if ($numRecentAchievements >= 0) {
        $achievementsQuery = Achievement::query()
            ->published()
            ->whereIn('GameID', $gameIDs)
            ->with(['game'])
            ->leftJoin('player_achievements', function ($join) use ($user) {
                $join->on('player_achievements.achievement_id', '=', 'Achievements.ID');
                $join->where('player_achievements.user_id', $user->id);
            })
            ->select(
                'Achievements.*',
                'player_achievements.unlocked_at',
                'player_achievements.unlocked_hardcore_at'
            )
            ->orderBy('player_achievements.unlocked_at', 'desc');

        // Only limit if we're not requesting all achievements.
        if ($numRecentAchievements > 0) {
            $achievementsQuery->whereNotNull('player_achievements.unlocked_at')
                ->orderBy('player_achievements.unlocked_at', 'desc');
        }

        $allAchievements = $achievementsQuery->get();

        // Group the results by game ID.
        $gameAchievementsMap = [];
        foreach ($gameIDs as $gameID) {
            $gameAchievements = $allAchievements->where('GameID', $gameID);

            if ($numRecentAchievements > 0) {
                $gameAchievements = $gameAchievements->take($numRecentAchievements);
            }

            $gameAchievementsMap[$gameID] = $gameAchievements;
        }

        foreach ($gameAchievementsMap as $gameID => $achievements) {
            foreach ($achievements as $achievement) {
                $gameData = $games->get($achievement->GameID)->toArray();

                if ($achievement->unlocked_hardcore_at) {
                    $unlockedAchievements[] = [
                        'Achievement' => $achievement->toArray(),
                        'When' => $achievement->unlocked_hardcore_at,
                        'Hardcore' => 1,
                        'Game' => $gameData,
                    ];
                } elseif ($achievement->unlocked_at) {
                    $unlockedAchievements[] = [
                        'Achievement' => $achievement->toArray(),
                        'When' => $achievement->unlocked_at,
                        'Hardcore' => 0,
                        'Game' => $gameData,
                    ];
                } else {
                    $lockedAchievements[] = [
                        'Achievement' => $achievement->toArray(),
                        'Game' => $gameData,
                    ];
                }
            }
        }
    }

    $libraryOut['Awarded'] = $awardedData;

    if ($withGameInfo) {
        $libraryOut['GameInfo'] = $gameInfo;
    }

    // magic numbers!
    // -1 = don't populate RecentAchievements field
    // 0 = return all achievements for each game, with the unlocked achievements first ordered by unlock date
    // >0 = return the N most recent unlocks across all games queried, grouped by game
    if ($numRecentAchievements >= 0) {
        usort($unlockedAchievements, function ($a, $b) {
            if ($a['When'] == $b['When']) {
                return $a['Achievement']['ID'] <=> $b['Achievement']['ID'];
            }

            return -($a['When'] <=> $b['When']);
        });

        if ($numRecentAchievements !== 0) {
            $unlockedAchievements = array_slice($unlockedAchievements, 0, $numRecentAchievements);
        }

        $recentAchievements = [];

        foreach ($unlockedAchievements as $unlockedAchievement) {
            $gameData = $unlockedAchievement['Game'];
            $gameID = (int) $gameData['ID'];
            $achievementData = $unlockedAchievement['Achievement'];
            $achievementID = (int) $achievementData['ID'];

            $recentAchievements[$gameID][$achievementID] = [
                'ID' => $achievementID,
                'GameID' => $gameID,
                'GameTitle' => $gameData['Title'],
                'Title' => $achievementData['Title'],
                'Description' => $achievementData['Description'],
                'Points' => (int) $achievementData['Points'],
                'Type' => $achievementData['type'],
                'BadgeName' => $achievementData['BadgeName'],
                'IsAwarded' => '1',
                'DateAwarded' => $unlockedAchievement['When'],
                'HardcoreAchieved' => (int) $unlockedAchievement['Hardcore'],
            ];
        }

        if ($numRecentAchievements === 0) {
            usort($lockedAchievements, function ($a, $b) {
                return $a['Achievement']['DisplayOrder'] <=> $b['Achievement']['DisplayOrder'];
            });

            foreach ($lockedAchievements as $lockedAchievement) {
                $gameData = $lockedAchievement['Game'];
                $gameID = (int) $gameData['ID'];
                $achievementData = $lockedAchievement['Achievement'];
                $achievementID = (int) $achievementData['ID'];

                $recentAchievements[$gameID][$achievementID] = [
                    'ID' => $achievementID,
                    'GameID' => $gameID,
                    'GameTitle' => $gameData['Title'],
                    'Title' => $achievementData['Title'],
                    'Description' => $achievementData['Description'],
                    'Points' => (int) $achievementData['Points'],
                    'Type' => $achievementData['type'],
                    'BadgeName' => $achievementData['BadgeName'],
                    'IsAwarded' => '0',
                    'DateAwarded' => null,
                    'HardcoreAchieved' => null,
                ];
            }
        }

        $libraryOut['RecentAchievements'] = $recentAchievements;
    }

    return $libraryOut;
}

function getUserAchievementUnlocksForGame(User|string $user, int $gameID, AchievementFlag $flag = AchievementFlag::OfficialCore): array
{
    $user = is_string($user) ? User::whereName($user)->first() : $user;

    $playerAchievements = $user
        ->playerAchievements()
        ->join('Achievements', 'Achievements.ID', '=', 'player_achievements.achievement_id')
        ->join('achievement_set_achievements', 'Achievements.ID', '=', 'achievement_set_achievements.achievement_id')
        ->join('achievement_sets', 'achievement_sets.id', '=', 'achievement_set_achievements.achievement_set_id')
        ->join('game_achievement_sets', 'game_achievement_sets.achievement_set_id', '=', 'achievement_sets.id')
        ->where('game_achievement_sets.game_id', $gameID)
        ->where('Flags', $flag->value)
        ->get([
            'player_achievements.achievement_id',
            'player_achievements.unlocked_at',
            'player_achievements.unlocked_hardcore_at',
        ])
        ->mapWithKeys(function ($unlock, int $key) {
            $result = [];

            // TODO move this transformation to where it's needed (web api) and use models everywhere else
            if ($unlock->unlocked_at) {
                $result['DateEarned'] = $unlock->unlocked_at->__toString();
            }

            if ($unlock->unlocked_hardcore_at) {
                $result['DateEarnedHardcore'] = $unlock->unlocked_hardcore_at->__toString();
            }

            return [$unlock->achievement_id => $result];
        });

    return $playerAchievements->toArray();
}

function reactivateUserEventAchievements(User $user, array $userUnlocks): array
{
    // unranked users can't participate in events
    if (!$user->isRanked()) {
        return $userUnlocks;
    }

    // find any active event achievements for the set of achievements that the user has already unlocked
    $activeEventAchievementMap = EventAchievement::active()
        ->whereIn('source_achievement_id', array_keys($userUnlocks))
        ->whereHas('achievement', function ($query) {
            $query->where('Flags', AchievementFlag::OfficialCore->value);
        })
        ->get(['source_achievement_id', 'achievement_id'])
        ->mapWithKeys(function ($eventAchievement, int $key) {
            return [$eventAchievement->achievement_id => $eventAchievement->source_achievement_id];
        })
        ->toArray();

    // no active event achievements found - we're done
    if (empty($activeEventAchievementMap)) {
        return $userUnlocks;
    }

    // see if the user has unlocked any of the active event achievements
    $playerUnlockedEventAchievementIds = $user->playerAchievements()
        ->whereIn('achievement_id', array_keys($activeEventAchievementMap))
        ->whereNotNull('unlocked_hardcore_at')
        ->pluck('achievement_id')
        ->toArray();

    // clear out the hardcore unlock date for the source achievement of each event
    // achievement the user has not unlocked
    foreach ($activeEventAchievementMap as $eventAchievementId => $achievementId) {
        if (!in_array($eventAchievementId, $playerUnlockedEventAchievementIds)) {
            unset($userUnlocks[$achievementId]['DateEarnedHardcore']);
        }
    }

    return $userUnlocks;
}

function GetAllUserProgress(User $user, int $consoleID): array
{
    /** @var Collection<int, Game> $games */
    $games = Game::where('ConsoleID', $consoleID)
        ->where('achievements_published', '>', 0)
        ->get();

    /** @var Collection<int, PlayerGame> $playerGames */
    $playerGames = $user->playerGames()
        ->whereIn('game_id', $games->pluck('id'))
        ->get()
        ->keyBy('game_id');

    $result = [];
    foreach ($games as $game) {
        /** @var ?PlayerGame $playerGame */
        $playerGame = $playerGames->get($game->id);

        $gameDetails = ['Achievements' => $game->achievements_published];

        if ($unlocked = $playerGame?->achievements_unlocked) {
            $gameDetails['Unlocked'] = $unlocked;

            if ($hardcore = $playerGame->achievements_unlocked_hardcore) {
                $gameDetails['UnlockedHardcore'] = $hardcore;
            }
        }

        $result[$game->id] = $gameDetails;
    }

    return $result;
}

function getUsersCompletedGamesAndMax(string $user): array
{
    if (!isValidUsername($user)) {
        return [];
    }

    $minAchievementsForCompletion = 5;

    $query = "SELECT gd.ID AS GameID, c.Name AS ConsoleName, c.ID AS ConsoleID,
            gd.ImageIcon, gd.Title, gd.sort_title as SortTitle, gd.achievements_published as MaxPossible,
            pg.first_unlock_at AS FirstWonDate, pg.last_unlock_at AS MostRecentWonDate,
            pg.achievements_unlocked AS NumAwarded, pg.achievements_unlocked_hardcore AS NumAwardedHC, " .
            floatDivisionStatement('pg.achievements_unlocked', 'gd.achievements_published') . " AS PctWon, " .
            floatDivisionStatement('pg.achievements_unlocked_hardcore', 'gd.achievements_published') . " AS PctWonHC
        FROM player_games AS pg
        LEFT JOIN GameData AS gd ON gd.ID = pg.game_id
        LEFT JOIN Console AS c ON c.ID = gd.ConsoleID
        LEFT JOIN UserAccounts ua ON ua.ID = pg.user_id
        WHERE (ua.User = :user OR ua.display_name = :user2)
        AND gd.achievements_published > $minAchievementsForCompletion
        ORDER BY PctWon DESC, PctWonHC DESC, MaxPossible DESC, gd.Title";

    return legacyDbFetchAll($query, ['user' => $user, 'user2' => $user])->toArray();
}

function getGameRecentPlayers(int $gameID, int $maximum_results = 10): array
{
    $retval = [];

    $sessions = GameRecentPlayer::with('user')
        ->where('game_id', $gameID)
        ->whereHas('user', function ($query) {
            $query->whereNull('banned_at');
        })
        ->orderBy('rich_presence_updated_at', 'DESC');

    if ($maximum_results) {
        $sessions = $sessions->limit($maximum_results);
    }

    foreach ($sessions->get() as $session) {
        $retval[] = [
            'UserID' => $session->user_id,
            'User' => $session->user->display_name,
            'Date' => $session->rich_presence_updated_at->__toString(),
            'Activity' => $session->rich_presence,
            'NumAwarded' => 0,
            'NumAwardedHardcore' => 0,
            'NumAchievements' => 0,
        ];
    }

    $mergePlayerGames = function (array &$retval) use ($gameID): array {
        $player_games = PlayerGame::where('game_id', $gameID)
            ->whereIn('user_id', array_column($retval, 'UserID'))
            ->select(['user_id', 'achievements_unlocked', 'achievements_unlocked_hardcore', 'achievements_total']);

        foreach ($player_games->get() as $player_game) {
            foreach ($retval as &$entry) {
                if ($entry['UserID'] == $player_game->user_id) {
                    $entry['NumAwarded'] = $player_game->achievements_unlocked;
                    $entry['NumAwardedHardcore'] = $player_game->achievements_unlocked_hardcore;
                    $entry['NumAchievements'] = $player_game->achievements_total;
                    break;
                }
            }
        }

        return $retval;
    };

    if ($maximum_results) {
        $maximum_results -= count($retval);
        if ($maximum_results == 0) {
            return $mergePlayerGames($retval);
        }
    }

    $userFilter = '';
    if (count($retval)) {
        $userFilter = 'AND ua.ID NOT IN (' . implode(',', array_column($retval, 'UserID')) . ')';
    }

    $query = "SELECT ua.ID as UserID, ua.User, ua.RichPresenceMsgDate AS Date, ua.RichPresenceMsg AS Activity
              FROM UserAccounts AS ua
              WHERE ua.LastGameID = $gameID AND ua.Permissions >= " . Permissions::Unregistered . "
              AND ua.RichPresenceMsgDate > TIMESTAMPADD(MONTH, -6, NOW()) $userFilter
              ORDER BY ua.RichPresenceMsgDate DESC";

    if ($maximum_results > 0) {
        $query .= " LIMIT $maximum_results";
    }

    foreach (legacyDbFetchAll($query) as $data) {
        $data['NumAwarded'] = 0;
        $data['NumAwardedHardcore'] = 0;
        $data['NumAchievements'] = 0;
        $retval[] = $data;
    }

    return $mergePlayerGames($retval);
}

/**
 * @deprecated use denormalized data from player_games
 */
function expireGameTopAchievers(int $gameID): void
{
    GameTopAchieversService::expireTopAchieversComponentData($gameID);
}
