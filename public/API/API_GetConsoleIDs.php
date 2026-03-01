<?php

use App\Models\System;
use App\Support\Cache\CacheKey;
use Illuminate\Support\Facades\Cache;

/*
 *  API_GetConsoleIDs - returns mapping of known consoles
 *    a : active - 1 for only active systems, 0 for all (default: 0)
 *    g : only game systems - 1 for only game systems, 0 for all system types (Events, Hubs, etc) (default: 0)
 *
 *  array
 *   object    [value]
 *    int       ID                  unique identifier of the console
 *    string    Name                name of the console
 *    string    IconURL             system icon URL
 *    bool      Active              indicates if the system is active in RA
 *    bool      IsGameSystem        indicates if the system is a game system (not Events, Hubs, etc.)
 */

$onlyActive = (int) (bool) request()->query('a', '0');
$onlyGameConsoles = (int) (bool) request()->query('g', '0');

$cacheKey = CacheKey::buildLegacyApiConsoleIdsCacheKey($onlyActive, $onlyGameConsoles);

$response = Cache::flexible($cacheKey, [1_800, 3_600], function () use ($onlyActive, $onlyGameConsoles) {
    $systems = System::query();
    if ($onlyGameConsoles) {
        $systems = $systems->gameSystems();
    }
    if ($onlyActive) {
        $systems = $systems->active();
    }

    return $systems->get()->map(fn ($system) => [
        'ID' => $system->id,
        'Name' => $system->name,
        'IconURL' => $system->icon_url,
        'Active' => boolval($system->active),
        'IsGameSystem' => System::isGameSystem($system->id),
    ])
        ->values()
        ->all();
});

return response()->json($response);
