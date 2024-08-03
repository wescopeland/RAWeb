<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript('UserResettableGameAchievement')]
class UserResettableGameAchievementData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public int $points,
        public bool $isHardcore,
    ) {
    }
}
