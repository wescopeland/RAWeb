<?php

declare(strict_types=1);

namespace App\Platform\Services;

use App\Site\Models\User;

class GameListService
{
    public function getUserProgressForGameIds(?User $user, array $gameIds): ?array
    {
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
