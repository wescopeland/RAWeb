<?php

declare(strict_types=1);

namespace App\Community\Listeners;

use App\Community\Enums\UserActivityType;
use App\Models\System;
use App\Models\User;
use App\Models\UserActivity;
use App\Platform\Events\AchievementSetBeaten;
use App\Platform\Events\AchievementSetCompleted;
use App\Platform\Events\LeaderboardEntryCreated;
use App\Platform\Events\LeaderboardEntryUpdated;
use App\Platform\Events\PlayerAchievementUnlocked;
use App\Platform\Events\PlayerGameAttached;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;

class WriteUserActivity
{
    /**
     * This will _only_ store UserActivity entries on users
     * Other side effects should be handled in dedicated listeners
     */
    public function handle(object $event): void
    {
        $storeActivity = true;
        $userActivityType = null;
        $subjectType = null;
        $subjectId = null;
        $context = null;
        $updateLastLogin = true;

        /** @var User $user */
        $user = $event->user;

        switch ($event::class) {
            case Login::class:
                /**
                 * login will only be called when user was logged out in-between
                 * ignore login activity within 6 hours after the last login activity
                 */
                $userActivityType = UserActivityType::Login;
                $storeActivity = $user->activities()
                    ->where('type', '=', $userActivityType)
                    ->where('created_at', '>', Carbon::now()->subHours(6))
                    ->doesntExist();
                break;
            case LeaderboardEntryCreated::class:
                $userActivityType = UserActivityType::NewLeaderboardEntry;
                // TODO: subject_context = create
                // TODO: subject_id
                $subjectType = 'leaderboard-entry';
                break;
            case LeaderboardEntryUpdated::class:
                $userActivityType = UserActivityType::ImprovedLeaderboardEntry;
                // TODO: subject_context = update
                // TODO: subject_id
                $subjectType = 'leaderboard-entry';
                break;
            case PlayerAchievementUnlocked::class:
                $userActivityType = UserActivityType::UnlockedAchievement;
                // TODO: subject_context = create
                $subjectType = 'achievement';
                $subjectId = $event->achievement->id;
                $context = $event->hardcore ? 1 : null;

                if (!System::isGameSystem($event->achievement->game->system->id)) {
                    // event unlocks should not update the user's LastLogin
                    $updateLastLogin = false;
                } else {
                    // Manual unlocks should not update the user's LastLogin.
                    $unlock = $user->playerAchievements()->firstWhere('achievement_id', $subjectId);
                    $isManualUnlock = $unlock && $unlock->unlocker_id !== 0;
                    $updateLastLogin = !$isManualUnlock;
                }

                break;
            case AchievementSetCompleted::class:
                $userActivityType = UserActivityType::CompleteAchievementSet;
                // TODO: subject_context = complete
                // TODO: subject_id
                // TODO $subjectType = 'achievement_set';
                break;
            case AchievementSetBeaten::class:
                $userActivityType = UserActivityType::BeatAchievementSet;
                // TODO: subject_context = beat
                // TODO: subject_id
                // TODO $subjectType = 'achievement_set';
                break;
            case PlayerGameAttached::class:
                $userActivityType = UserActivityType::StartedPlaying;
                $subjectType = 'game';
                $subjectId = $event->game->id ?? null;
                $storeActivity = !empty($subjectId);

                // don't update user's LastLogin when creating player_game entries for events
                $updateLastLogin = System::isGameSystem($event->game->system->id);
                break;
            default:
        }

        if ($userActivityType && $storeActivity) {
            $user->activities()->save(new UserActivity([
                'type' => $userActivityType,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'subject_context' => $context,
            ]));
        }

        if ($updateLastLogin) {
            /*
            * update last activity timestamp regardless of whether an activity was stored as some might have been suppressed
            */
            $user->LastLogin = Carbon::now();
            $user->saveQuietly();
        }
    }
}
