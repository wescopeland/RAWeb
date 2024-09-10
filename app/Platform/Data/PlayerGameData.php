<?php

declare(strict_types=1);

namespace App\Platform\Data;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\LaravelData\Data;
use Illuminate\Support\Carbon;

#[TypeScript('PlayerGame')]
class PlayerGameData extends Data
{
    public function __construct(
        public int $id,
        public int $achievementsUnlocked,
        public ?int $achievementsUnlockedHardcore,
        public ?int $achievementsUnlockedSoftcore,
        public ?Carbon $beatenAt,
        public ?Carbon $beatenHardcoreAt,
        public ?Carbon $completedAt,
        public ?Carbon $completedHardcoreAt,
        public ?int $points,
        public ?int $pointsHardcore,
    ) {
    }
}
