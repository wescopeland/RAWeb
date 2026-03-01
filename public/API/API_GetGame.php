<?php

/*
 *  API_GetGame - returns information about a game
 *    i : game id
 *
 *  string     Title                      name of the game
 *  string     GameTitle                  name of the game
 *  int        ConsoleID                  unique identifier of the console associated to the game
 *  string     ConsoleName                name of the console associated to the game
 *  string     Console                    name of the console associated to the game
 *  int        ForumTopicID               unique identifier of the official forum topic for the game
 *  int        Flags                      always "0"
 *  string     GameIcon                   site-relative path to the game's icon image
 *  string     ImageIcon                  site-relative path to the game's icon image
 *  string     ImageTitle                 site-relative path to the game's title image
 *  string     ImageIngame                site-relative path to the game's in-game image
 *  string     ImageBoxArt                site-relative path to the game's box art image
 *  string     Publisher                  publisher information for the game
 *  string     Developer                  developer information for the game
 *  string     Genre                      genre information for the game
 *  string?    Released                   a YYYY-MM-DD date of the game's earliest release date, or null. also see ReleasedAtGranularity.
 *  string?    ReleasedAtGranularity      how precise the Released value is. possible values are "day", "month", "year", and null.
 */

use App\Models\Game;
use App\Support\Cache\CacheKey;
use Illuminate\Support\Facades\Cache;

$gameId = (int) request()->query('i');

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

return response()->json([
    'Title' => $baseData['title'],
    'GameTitle' => $baseData['title'],
    'ConsoleID' => $baseData['system_id'],
    'ConsoleName' => $baseData['system_name'],
    'Console' => $baseData['system_name'],
    'ForumTopicID' => $baseData['forum_topic_id'],
    'Flags' => 0,
    'GameIcon' => $baseData['image_icon'],
    'ImageIcon' => $baseData['image_icon'],
    'ImageTitle' => $baseData['image_title'],
    'ImageIngame' => $baseData['image_ingame'],
    'ImageBoxArt' => $baseData['image_box_art'],
    'Publisher' => $baseData['publisher'],
    'Developer' => $baseData['developer'],
    'Genre' => $baseData['genre'],
    'Released' => $baseData['released_at'],
    'ReleasedAtGranularity' => $baseData['released_at_granularity'],
]);
