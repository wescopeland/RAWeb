<?php

declare(strict_types=1);

namespace App\Platform\Services;

class GameListService
{
    public function getUserProgressForGameIds(array $gameIds): ?array
    {
        $user = request()->user();

        if (!$user) {
            return null;
        }

        return $user->playerGames()
            ->whereIn('game_id', $gameIds)
            ->get(['game_id', 'achievements_unlocked', 'achievements_unlocked_hardcore'])
            ->mapWithKeys(function ($game) {
                return [
                    $game->game_id => [
                        'achievements_unlocked' => $game->achievements_unlocked,
                        'achievements_unlocked_hardcore' => $game->achievements_unlocked_hardcore,
                    ],
                ];
            })
            ->toArray();
    }
}
