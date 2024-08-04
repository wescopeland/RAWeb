<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\Permissions;
use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;

#[TypeScript('User')]
class UserData extends Data
{
    public function __construct(
        public string $displayName,
        public string $avatarUrl,
        public bool $isMuted,

        public Lazy|int $id,
        public Lazy|string $username,
        public Lazy|string $motto,
        public Lazy|int $legacyPermissions,

        #[TypeScriptType([
            'prefersAbsoluteDates' => 'boolean',
        ])]
        public Lazy|array $preferences,

        #[LiteralTypeScriptType('App.Models.UserRole[]')]
        public Lazy|array $roles,

        public Lazy|string $apiKey,
        public Lazy|string $deleteRequested,
        public Lazy|string $emailAddress,
        public Lazy|int $unreadMessageCount,
        public Lazy|bool $userWallActive,
        public Lazy|string|null $visibleRole,
        public Lazy|int $websitePrefs,
    ) {
    }

    public static function fromUser(User $user): self
    {
        $legacyPermissions = (int) $user->getAttribute('Permissions');

        return new self(
            displayName: $user->display_name,
            avatarUrl: $user->avatar_url,
            isMuted: $user->isMuted(),

            id: Lazy::create(fn () => $user->id),
            username: Lazy::create(fn () => $user->username),
            motto: Lazy::create(fn () => $user->Motto),
            legacyPermissions: Lazy::create(fn () => $legacyPermissions),
            preferences: Lazy::create(
                fn () => [
                    'prefersAbsoluteDates' => $user->prefers_absolute_dates,
                ]
            ),
            roles: Lazy::create(fn () => $user->getRoleNames()->toArray()),

            apiKey: Lazy::create(fn () => $user->APIKey),
            deleteRequested: Lazy::create(fn () => $user->DeleteRequested),
            emailAddress: Lazy::create(fn () => $user->EmailAddress),
            unreadMessageCount: Lazy::create(fn () => $user->UnreadMessageCount),
            userWallActive: Lazy::create(fn () => $user->UserWallActive),
            visibleRole: Lazy::create(fn () => $legacyPermissions > 1 ? Permissions::toString($legacyPermissions) : null),
            websitePrefs: Lazy::create(fn () => $user->websitePrefs),
        );
    }
}
