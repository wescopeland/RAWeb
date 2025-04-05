<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GameSet;
use App\Platform\Enums\GameSetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameSet>
 */
class GameSetFactory extends Factory
{
    private static int $sequence = 1;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Use a sequence to ensure unique titles and stave off test flake.
        $title = ucwords(fake()->words(3, true) . ' ' . self::$sequence++);

        return [
            'title' => $title,
            'game_id' => null,
            'image_asset_path' => '/Images/000001.png',
            'type' => GameSetType::Hub,
        ];
    }
}
