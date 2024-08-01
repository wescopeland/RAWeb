<?php

declare(strict_types=1);

namespace App\Data;

use App\Http\Requests\ProfileSettingsRequest;
use Spatie\LaravelData\Data;

class ProfileSettingsData extends Data
{
    public function __construct(
        public string $motto,
        public bool $userWallActive,
    ) {
    }

    public static function fromRequest(ProfileSettingsRequest $request): self
    {
        return new self(
            motto: $request->motto ?? '',
            userWallActive: $request->userWallActive,
        );
    }

    public function toArray(): array
    {
        return [
            'Motto' => $this->motto,
            'UserWallActive' => $this->userWallActive,
        ];
    }
}
