<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use App\Models\UserGameListEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserGameListEntry>
 */
class UserGameListEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'achievement_set_request',
            'GameID' => Game::factory(),
        ];
    }
}
