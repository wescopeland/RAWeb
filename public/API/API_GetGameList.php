<?php

/*
 *  API_GetGameList - returns games for the specified console
 *    i : console id
 *    f : 1=only return games where NumAchievements > 0 (default: 0)
 *    h : 1=also return hashes (default: 0)
 *    o : offset (optional)
 *    c : count (optional)
 *
 *  array
 *   object     [value]
 *    int        ID                unique identifier of the game
 *    string     Title             title of the game
 *    int        ConsoleID         unique identifier of the console
 *    string     ConsoleName       name of the console
 *    string     ImageIcon         site-relative path to the game's icon image
 *    int        NumAchievements   number of core achievements for the game
 *    int        NumLeaderboards   number of leaderboards for the game
 *    int        Points            total number of points the game's achievements are worth
 *    datetime   DateModified      when the last modification was made
 *                                 NOTE: this only tracks modifications to the achievements of the game,
 *                                       but is consistent with the data reported in the site game list.
 *    ?int       ForumTopicID      unique identifier of the official forum topic for the game
 *    array      Hashes
 *     string     [value]          RetroAchievements hash associated to the game
 */

use App\Models\Achievement;
use App\Models\Game;
use App\Models\GameHash;
use App\Models\Leaderboard;
use App\Support\Cache\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

$consoleID = (int) request()->query('i');
if ($consoleID <= 0) {
    return response()->json(['success' => false]);
}

$withAchievements = (bool) request()->query('f');
$withHashes = (bool) request()->query('h');
$offset = (int) request()->query('o');
$count = (int) request()->query('c');

// Cache the full game list per console. Filtering and pagination are applied in PHP.
$allGames = Cache::flexible(CacheKey::buildLegacyApiGameListCacheKey($consoleID), [600, 1_800], function () use ($consoleID) {
    $gameIdsSubquery = Game::query()
        ->select('id')
        ->where('system_id', $consoleID);

    $achievementsSubquery = Achievement::query()
        ->selectRaw('game_id as GameID, MAX(modified_at) as DateModified')
        ->whereIn('game_id', $gameIdsSubquery)
        ->groupBy('game_id');

    $leaderboardsSubquery = Leaderboard::query()
        ->selectRaw('game_id, COUNT(*) as NumLBs')
        ->whereIn('game_id', $gameIdsSubquery)
        ->groupBy('game_id');

    $queryResponse = DB::table('games')
        ->leftJoin('systems AS s', 's.id', '=', 'games.system_id')
        ->leftJoinSub($achievementsSubquery, 'ach_data', function ($join) {
            $join->on('ach_data.GameID', '=', 'games.id');
        })
        ->leftJoinSub($leaderboardsSubquery, 'lb_data', function ($join) {
            $join->on('lb_data.game_id', '=', 'games.id');
        })
        ->select(
            'games.id',
            'games.title',
            'games.system_id',
            'games.image_icon_asset_path',
            'games.points_total',
            'games.forum_topic_id',
            's.name as ConsoleName',
            DB::raw('COALESCE(games.achievements_published, 0) AS NumAchievements'),
            DB::raw('ach_data.DateModified AS DateModified'),
            DB::raw('COALESCE(lb_data.NumLBs, 0) AS NumLBs')
        )
        ->where('games.system_id', $consoleID)
        ->orderBy('games.title', 'asc')
        ->get();

    $results = [];
    foreach ($queryResponse as $game) {
        $results[] = [
            'Title' => $game->title,
            'ID' => $game->id,
            'ConsoleID' => $game->system_id,
            'ConsoleName' => $game->ConsoleName,
            'ImageIcon' => $game->image_icon_asset_path,
            'NumAchievements' => (int) $game->NumAchievements,
            'NumLeaderboards' => $game->NumLBs ?? 0,
            'Points' => $game->points_total ?? 0,
            'DateModified' => $game->DateModified,
            'ForumTopicID' => $game->forum_topic_id,
        ];
    }

    return $results;
});

$response = $allGames;

if ($withAchievements) {
    $response = array_values(array_filter($response, fn ($game) => $game['NumAchievements'] > 0));
}

if ($offset > 0 || $count > 0) {
    $response = array_slice($response, $offset, $count > 0 ? $count : null);
}

// Hashes change frequently and are only requested by a subset of callers, so fetch live.
if ($withHashes) {
    $gameIds = array_column($response, 'ID');

    foreach ($response as &$entry) {
        $entry['Hashes'] = [];
    }
    unset($entry);

    if (!empty($gameIds)) {
        $responseIndex = [];
        foreach ($response as $index => $entry) {
            $responseIndex[$entry['ID']] = $index;
        }

        $hashes = GameHash::compatible()
            ->select('game_id', 'md5')
            ->whereIn('game_id', $gameIds)
            ->orderBy('game_id');

        foreach ($hashes->get() as $hash) {
            $response[$responseIndex[$hash->game_id]]['Hashes'][] = $hash->md5;
        }
    }
}

return response()->json($response);
