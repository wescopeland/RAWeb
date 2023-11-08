<?php

declare(strict_types=1);

namespace App\Platform\Enums;

abstract class RankingType
{
    public const GamesBeatenHardcoreDemos = 'games_beaten_hardcore_demos';

    public const GamesBeatenHardcoreHacks = 'games_beaten_hardcore_hacks';

    public const GamesBeatenHardcoreHomebrew = 'games_beaten_hardcore_homebrew';

    public const GamesBeatenHardcorePrototypes = 'games_beaten_hardcore_prototypes';

    public const GamesBeatenHardcoreRetail = 'games_beaten_hardcore_retail';

    public const GamesBeatenHardcoreUnlicensed = 'games_beaten_hardcore_unlicensed';

    public static function cases(): array
    {
        return [
            self::GamesBeatenHardcoreDemos,
            self::GamesBeatenHardcoreHacks,
            self::GamesBeatenHardcoreHomebrew,
            self::GamesBeatenHardcorePrototypes,
            self::GamesBeatenHardcoreRetail,
            self::GamesBeatenHardcoreUnlicensed,
        ];
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::cases());
    }
}
