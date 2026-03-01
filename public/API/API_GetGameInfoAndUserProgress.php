<?php

/*
 *  API_GetGameInfoAndUserProgress
 *    g : game id
 *    u : username or user ULID
 *    a : if 1, include highest award metadata (default: 0)
 *
 *  int        ID                         unique identifier of the game
 *  string     Title                      name of the game
 *  int        ConsoleID                  unique identifier of the console associated to the game
 *  string     ConsoleName                name of the console associated to the game
 *  int?       ParentGameID               unique identifier of the parent game if this is a subset
 *  int        NumDistinctPlayers         number of unique players who have earned achievements for the game
 *  int        NumDistinctPlayersCasual   [deprecated] equal to NumDistinctPlayers
 *  int        NumDistinctPlayersHardcore [deprecated] equal to NumDistinctPlayers
 *  int        NumAchievements            count of core achievements associated to the game
 *  int        NumAwardedToUser           number of achievements earned by the user
 *  int        NumAwardedToUserHardcore   number of achievements earned by the user in hardcore
 *  string     UserCompletion             percentage of achievements earned by the user
 *  string     UserCompletionHardcore     percentage of achievements earned by the user in hardcore
 *  int        UserTotalPlaytime          total time the user has spent playing the game, in seconds
 *  map        Achievements
 *   string     [key]                     unique identifier of the achievement
 *    int        ID                       unique identifier of the achievement
 *    string     Title                    title of the achievement
 *    string     Description              description of the achievement
 *    int        Points                   number of points the achievement is worth
 *    int        TrueRatio                number of RetroPoints ("white points") the achievement is worth
 *    string     BadgeName                unique identifier of the badge image for the achievement
 *    string?    Type                     "progression", "win_condition", "missable" or null
 *    int        NumAwarded               number of times the achievement has been awarded
 *    int        NumAwardedHardcore       number of times the achievement has been awarded in hardcore
 *    int        DisplayOrder             field used for determining which order to display the achievements
 *    string     Author                   user who originally created the achievement
 *    string     AuthorULID               queryable stable unique identifier of the user who first created the achievement
 *    datetime   DateCreated              when the achievement was created
 *    datetime   DateModified             when the achievement was last modified
 *    string     MemAddr                  md5 of the logic for the achievement
 *    datetime   DateEarned               when the achievement was earned by the user
 *    datetime   DateEarnedHardcore       when the achievement was earned by the user in hardcore
 *  int        ForumTopicID               unique identifier of the official forum topic for the game
 *  int        Flags                      always "0"
 *  string     ImageIcon                  site-relative path to the game's icon image
 *  string     ImageTitle                 site-relative path to the game's title image
 *  string     ImageIngame                site-relative path to the game's in-game image
 *  string     ImageBoxArt                site-relative path to the game's box art image
 *  string     Publisher                  publisher information for the game
 *  string     Developer                  developer information for the game
 *  string     Genre                      genre information for the game
 *  string?    Released                   a YYYY-MM-DD date of the game's earliest release date, or null. also see ReleasedAtGranularity.
 *  string?    ReleasedAtGranularity      how precise the Released value is. possible values are "day", "month", "year", and null.
 *  bool       IsFinal                    deprecated, will always be false
 *  string     RichPresencePatch          md5 of the script for generating the rich presence for the game
 *  ?string    HighestAwardKind           "mastered", "completed", "beaten-hardcore", "beaten-softcore", or null. requires the 'a' query param to be 1.
 *  ?datetime  HighestAwardDate           an ISO8601 timestamp string, or null, for when the HighestAwardKind was granted. requires the 'a' query param to be 1.
 */

use App\Actions\FindUserByIdentifierAction;
use App\Models\Achievement;
use App\Models\Game;
use App\Models\PlayerBadge;
use App\Support\Cache\CacheKey;
use App\Support\Rules\ValidUserIdentifier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

$input = Validator::validate(Arr::wrap(request()->query()), [
    'g' => ['required', 'min:1'],
    'u' => ['required', new ValidUserIdentifier()],
]);

$targetUser = (new FindUserByIdentifierAction())->execute($input['u']);
if (!$targetUser) {
    return response()->json([]);
}

$gameId = (int) $input['g'];

// This shared cache is also warmed/read by API_GetGame and API_GetGameExtended.
$baseData = Cache::flexible(CacheKey::buildLegacyApiGameBaseDataCacheKey($gameId), [600, 1_800], function () use ($gameId) {
    $game = Game::with('system')->find($gameId);

    if (!$game) {
        return null;
    }

    return [
        'id' => $game->id,
        'title' => $game->title,
        'system_id' => $game->system_id,
        'system_name' => $game->system->name,
        'forum_topic_id' => $game->forum_topic_id,
        'image_icon' => $game->image_icon_asset_path,
        'image_title' => $game->image_title_asset_path,
        'image_ingame' => $game->image_ingame_asset_path,
        'image_box_art' => $game->image_box_art_asset_path,
        'publisher' => $game->publisher,
        'developer' => $game->developer,
        'genre' => $game->genre,
        'released_at' => $game->released_at?->format('Y-m-d'),
        'released_at_granularity' => $game->released_at_granularity?->value,
        'trigger_definition_md5' => md5($game->trigger_definition ?? ''),
        'parent_game_id' => $game->parentGameId,
        'players_total' => $game->players_total,
        'achievements_published' => $game->achievements_published,
        'updated_at' => $game->updated_at->format('Y-m-d\TH:i:s.u\Z'),
        'legacy_guide_url' => $game->legacy_guide_url,
    ];
});

if (!$baseData) {
    return response()->json([]);
}

// User-specific data must always be live.
$playerGame = $targetUser->playerGames()->where('game_id', $gameId)->first();

$gameData = [
    'ID' => $baseData['id'],
    'Title' => $baseData['title'],
    'ConsoleID' => $baseData['system_id'],
    'ConsoleName' => $baseData['system_name'],
    'ParentGameID' => $baseData['parent_game_id'],
    'NumDistinctPlayers' => $baseData['players_total'],
    'NumDistinctPlayersCasual' => $baseData['players_total'],
    'NumDistinctPlayersHardcore' => $baseData['players_total'],
    'NumAchievements' => $baseData['achievements_published'],
    'NumAwardedToUser' => $playerGame->achievements_unlocked ?? 0,
    'NumAwardedToUserHardcore' => $playerGame->achievements_unlocked_hardcore ?? 0,
    'UserCompletion' => sprintf("%01.2f%%", ($playerGame->completion_percentage ?? 0) * 100),
    'UserCompletionHardcore' => sprintf("%01.2f%%", ($playerGame->completion_percentage_hardcore ?? 0) * 100),
    'UserTotalPlaytime' => $playerGame->playtime_total ?? 0,
    'ForumTopicID' => $baseData['forum_topic_id'],
    'Flags' => 0,
    'ImageIcon' => $baseData['image_icon'],
    'ImageTitle' => $baseData['image_title'],
    'ImageIngame' => $baseData['image_ingame'],
    'ImageBoxArt' => $baseData['image_box_art'],
    'Publisher' => $baseData['publisher'],
    'Developer' => $baseData['developer'],
    'Genre' => $baseData['genre'],
    'Released' => $baseData['released_at'],
    'ReleasedAtGranularity' => $baseData['released_at_granularity'],
    'IsFinal' => false,
    'RichPresencePatch' => $baseData['trigger_definition_md5'],
];

if (!$baseData['achievements_published']) {
    $gameData['Achievements'] = new stdClass();
} else {
    // This shared cache is also warmed/read by API_GetGameExtended.
    $cachedAchievements = Cache::flexible(CacheKey::buildLegacyApiGameAchievementsCacheKey($gameId, true), [300, 900], function () use ($gameId) {
        $achievements = Achievement::where('game_id', $gameId)
            ->where('is_promoted', true)
            ->with('developer')
            ->orderBy('order_column')
            ->get();

        return $achievements->map(fn ($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'description' => $a->description,
            'points' => $a->points,
            'points_weighted' => $a->points_weighted,
            'type' => $a->type,
            'image_name' => $a->image_name,
            'unlocks_total' => $a->unlocks_total,
            'unlocks_hardcore' => $a->unlocks_hardcore,
            'order_column' => $a->order_column,
            'developer_name' => $a->developer?->display_name,
            'developer_ulid' => $a->developer?->ulid,
            'created_at' => $a->created_at->format('Y-m-d H:i:s'),
            'modified_at' => $a->modified_at->format('Y-m-d H:i:s'),
            'trigger_definition_md5' => md5($a->trigger_definition ?? ''),
        ])->all();
    });

    $achievements = [];
    foreach ($cachedAchievements as $a) {
        $achievements[strval($a['id'])] = [
            'ID' => $a['id'],
            'Title' => $a['title'],
            'Description' => $a['description'],
            'Points' => $a['points'],
            'TrueRatio' => $a['points_weighted'],
            'Type' => $a['type'],
            'BadgeName' => $a['image_name'],
            'NumAwarded' => $a['unlocks_total'],
            'NumAwardedHardcore' => $a['unlocks_hardcore'],
            'DisplayOrder' => $a['order_column'],
            'Author' => $a['developer_name'],
            'AuthorULID' => $a['developer_ulid'],
            'DateCreated' => $a['created_at'],
            'DateModified' => $a['modified_at'],
            'MemAddr' => $a['trigger_definition_md5'],
        ];
    }

    // User unlock dates are always live.
    $playerAchievements = $targetUser->playerAchievements()->whereIn('achievement_id', array_keys($achievements))->get();
    foreach ($playerAchievements as $playerAchievement) {
        $idStr = strval($playerAchievement->achievement_id);

        $achievements[$idStr]['DateEarned'] = $playerAchievement->unlocked_at->format('Y-m-d H:i:s');
        if ($playerAchievement->unlocked_hardcore_at) {
            $achievements[$idStr]['DateEarnedHardcore'] = $playerAchievement->unlocked_hardcore_at->format('Y-m-d H:i:s');
        }
    }

    $gameData['Achievements'] = $achievements;
}

$includeAwardMetadata = request()->query('a', '0');
if ($includeAwardMetadata == '1') {
    $highestAwardMetadata = PlayerBadge::getHighestUserAwardForGameId($targetUser, $gameId);

    if ($highestAwardMetadata) {
        $gameData['HighestAwardKind'] = $highestAwardMetadata['highestAwardKind'];
        $gameData['HighestAwardDate'] = $highestAwardMetadata['highestAward']->awarded_at->toIso8601String();
    } else {
        $gameData['HighestAwardKind'] = null;
        $gameData['HighestAwardDate'] = null;
    }
}

return response()->json($gameData);
