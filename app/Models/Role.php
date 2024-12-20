<?php

declare(strict_types=1);

namespace App\Models;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript('UserRole')]
class Role extends \Spatie\Permission\Models\Role
{
    /*
     * Providers Traits
     */
    use PivotEventTrait;

    // roles

    public const ROOT = 'root';

    // admin roles assigned by root

    public const ADMINISTRATOR = 'administrator';

    public const RELEASE_MANAGER = 'release-manager';

    // creator roles assigned by admin

    public const GAME_HASH_MANAGER = 'game-hash-manager';

    public const DEVELOPER_STAFF = 'developer-staff'; // staff

    public const DEVELOPER = 'developer';

    public const DEVELOPER_JUNIOR = 'developer-junior';

    public const ARTIST = 'artist';

    public const WRITER = 'writer';

    public const GAME_EDITOR = 'game-editor';

    public const PLAY_TESTER = 'play-tester';

    // moderation roles assigned by admin

    public const MODERATOR = 'moderator';

    public const FORUM_MANAGER = 'forum-manager';

    public const TICKET_MANAGER = 'ticket-manager';

    public const NEWS_MANAGER = 'news-manager';

    public const EVENT_MANAGER = 'event-manager';

    public const CHEAT_INVESTIGATOR = 'cheat-investigator';

    // vanity roles assigned by root

    public const FOUNDER = 'founder';

    public const ARCHITECT = 'architect';

    public const ENGINEER = 'engineer';

    public const TEAM_ACCOUNT = 'team-account';

    public const BETA = 'beta';

    // public const SUPPORTER = 'supporter';

    // public const CONTRIBUTOR = 'contributor';

    // vanity roles assigned by admin

    public const DEVELOPER_RETIRED = 'developer-retired';

    public static function toFilamentColor(string $role): string
    {
        return match ($role) {
            Role::ROOT => 'gray',

            // admin roles assigned by root

            Role::ADMINISTRATOR => 'danger',
            Role::RELEASE_MANAGER => 'warning',

            // creator roles assigned by admin

            Role::GAME_HASH_MANAGER => 'warning',
            Role::DEVELOPER_STAFF => 'success',
            Role::DEVELOPER => 'success',
            Role::DEVELOPER_JUNIOR => 'success',
            Role::ARTIST => 'success',
            Role::WRITER => 'success',
            Role::GAME_EDITOR => 'success',
            Role::PLAY_TESTER => 'success',

            // moderation roles assigned by admin

            Role::MODERATOR => 'warning',
            Role::FORUM_MANAGER => 'info',
            Role::TICKET_MANAGER => 'info',
            Role::NEWS_MANAGER => 'info',
            Role::EVENT_MANAGER => 'info',
            Role::CHEAT_INVESTIGATOR => 'info',

            // vanity roles assigned by root

            Role::FOUNDER => 'primary',
            Role::ARCHITECT => 'primary',
            Role::ENGINEER => 'primary',
            Role::TEAM_ACCOUNT => 'primary',
            Role::BETA => 'primary',

            // vanity roles assigned by admin

            Role::DEVELOPER_RETIRED => 'primary',
            default => 'gray',
        };
    }

    public static function boot()
    {
        parent::boot();

        // record users role attach/detach in audit log

        static::pivotAttached(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
            if ($relationName === 'users') {
                foreach ($pivotIds as $pivotId) {
                    $user = User::find($pivotId);
                    activity()->causedBy(auth()->user())->performedOn($user)
                        ->withProperty('relationships', ['roles' => [$model->id]])
                        ->withProperty('attributes', ['roles' => [$model->id => []]])
                        ->event('pivotAttached')
                        ->log('pivotAttached');
                }
            }
        });

        static::pivotDetached(function ($model, $relationName, $pivotIds) {
            if ($relationName === 'users') {
                foreach ($pivotIds as $pivotId) {
                    $user = User::find($pivotId);
                    activity()->causedBy(auth()->user())->performedOn($user)
                        ->withProperty('relationships', ['roles' => [$model->id]])
                        ->event('pivotDetached')
                        ->log('pivotDetached');
                }
            }
        });
    }

    public function getTitleAttribute(): string
    {
        return __('permission.role.' . $this->name);
    }
}
