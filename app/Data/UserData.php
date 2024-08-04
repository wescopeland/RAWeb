<?php

declare(strict_types=1);

namespace App\Data;

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
        public Lazy|int $websitePrefs,

        // Permissions
        public Lazy|bool $canUpdateAvatar,
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            displayName: $user->display_name,
            avatarUrl: $user->avatar_url,

            id: Lazy::create(fn () => $user->id),
            username: Lazy::create(fn () => $user->username),
            motto: Lazy::create(fn () => $user->Motto),
            legacyPermissions: Lazy::create(fn () => (int) $user->getAttribute('Permissions')),
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
            websitePrefs: Lazy::create(fn () => $user->websitePrefs),

            // Permissions
            canUpdateAvatar: Lazy::create(fn () => $user->can('updateAvatar', $user)),
        );
    }
}
