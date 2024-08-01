<?php

declare(strict_types=1);

namespace App\Data;

use App\Http\Requests\WebsitePrefsRequest;
use Spatie\LaravelData\Data;

class WebsitePrefsData extends Data
{
    public function __construct(
        public int $websitePrefs,
    ) {
    }

    public static function fromRequest(WebsitePrefsRequest $request): self
    {
        return new self(
            websitePrefs: $request->websitePrefs,
        );
    }

    public function toArray(): array
    {
        return [
            'websitePrefs' => $this->websitePrefs,
        ];
    }
}
