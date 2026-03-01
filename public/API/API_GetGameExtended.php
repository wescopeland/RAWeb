<?php

/*
 *  API_GetGameExtended - returns information about a game
 *    i : game id
 *    f : flag - 3 for core achievements, 5 for unofficial (default: 3)
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
 *  map        Achievements
 *   string     [key]                     unique identifier of the achievement
 *    int        ID                       unique identifier of the achievement
 *    string     Title                    title of the achievement
 *    string     Description              description of the achievement
 *    int        Points                   number of points the achievement is worth
 *    int        TrueRatio                number of RetroPoints ("white points") the achievement is worth
 *    string     BadgeName                unique identifier of the badge image for the achievement
 *    int        NumAwarded               number of times the achievement has been awarded
 *    int        NumAwardedHardcore       number of times the achievement has been awarded in hardcore
 *    int        DisplayOrder             field used for determining which order to display the achievements
 *    string     Author                   user who originally created the achievement
 *    string     AuthorULID               queryable stable unique identifier of the user who first created the achievement
 *    datetime   DateCreated              when the achievement was created
 *    datetime   DateModified             when the achievement was last modified
 *    string     MemAddr                  md5 of the logic for the achievement
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
 *  array      Claims
 *   object    [value]
 *    string    User                      user holding the claim
 *    string    ULID                      queryable stable unique identifier of the user holding the claim
 *    int       SetType                   set type claimed: 0 - new set, 1 - revision
 *    int       ClaimType                 claim type: 0 - primary, 1 - collaboration
 *    string    Created                   date the claim was made
 *    string    Expiration                date the claim will expire
 */

use App\Models\Achievement;
use App\Models\AchievementSetClaim;
use App\Models\Game;
use App\Support\Cache\CacheKey;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$input = Validator::validate(Arr::wrap(request()->query()), [
    'i' => ['required', 'integer', 'min:1'],
    'f' => [
        'nullable',
        Rule::in([(string) Achievement::FLAG_PROMOTED, (string) Achievement::FLAG_UNPROMOTED]),
    ],
], [
    'f.in' => 'Invalid flag parameter. Valid values are ' . Achievement::FLAG_PROMOTED . ' (published) or ' . Achievement::FLAG_UNPROMOTED . ' (unpublished).',
]);

$gameId = (int) $input['i'];
$isPromoted = Achievement::isPromotedFromLegacyFlags((int) ($input['f'] ?? (string) Achievement::FLAG_PROMOTED));

// This shared cache is also warmed/read by API_GetGame and API_GetGameInfoAndUserProgress.
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
    return response()->json();
}

$cachedAchievements = Cache::flexible(CacheKey::buildLegacyApiGameAchievementsCacheKey($gameId, $isPromoted), [300, 900], function () use ($gameId, $isPromoted) {
    $achievements = Achievement::where('game_id', $gameId)
        ->where('is_promoted', $isPromoted)
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

$gameAchievementSetClaims = AchievementSetClaim::with('user')->where('game_id', $gameId)->get();

$gameData = [
    'ID' => $baseData['id'],
    'Title' => $baseData['title'],
    'ConsoleID' => $baseData['system_id'],
    'ForumTopicID' => $baseData['forum_topic_id'],
    'Flags' => null,
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
    'GuideURL' => $baseData['legacy_guide_url'],
    'Updated' => $baseData['updated_at'],
];

if (!empty($cachedAchievements)) {
    $gameListAchievements = [];
    foreach ($cachedAchievements as $a) {
        $gameListAchievements[$a['id']] = [
            'ID' => $a['id'],
            'NumAwarded' => $a['unlocks_total'],
            'NumAwardedHardcore' => $a['unlocks_hardcore'],
            'Title' => $a['title'],
            'Description' => $a['description'],
            'Points' => $a['points'],
            'TrueRatio' => $a['points_weighted'],
            'Author' => $a['developer_name'],
            'AuthorULID' => $a['developer_ulid'],
            'DateModified' => $a['modified_at'],
            'DateCreated' => $a['created_at'],
            'BadgeName' => $a['image_name'],
            'DisplayOrder' => $a['order_column'],
            'MemAddr' => $a['trigger_definition_md5'],
            'type' => $a['type'],
        ];
    }
} else {
    $gameListAchievements = new ArrayObject();
}

if ($gameAchievementSetClaims->isEmpty()) {
    $gameClaims = [];
} else {
    $gameClaims = $gameAchievementSetClaims->map(function ($gc) {
        return [
            'User' => $gc->user->display_name,
            'ULID' => $gc->user->ulid,
            'SetType' => $gc->set_type->toLegacyInteger(),
            'GameID' => $gc->game_id,
            'ClaimType' => $gc->claim_type->toLegacyInteger(),
            'Created' => $gc->created_at->format('Y-m-d H:i:s'),
            'Expiration' => $gc->finished_at->format('Y-m-d H:i:s'),
        ];
    });
}

return response()->json(array_merge(
    $gameData,
    [
        'ConsoleName' => $baseData['system_name'],
        'ParentGameID' => $baseData['parent_game_id'],
        'NumDistinctPlayers' => $baseData['players_total'],
        'NumAchievements' => count($cachedAchievements),
        'Achievements' => $gameListAchievements,
        'Claims' => $gameClaims,
        'NumDistinctPlayersCasual' => $baseData['players_total'],
        'NumDistinctPlayersHardcore' => $baseData['players_total'],
    ]
));
