<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Community\Enums\ArticleType;
use App\Data\UpdateEmailData;
use App\Data\UpdatePasswordData;
use App\Data\UpdateProfileData;
use App\Data\UpdateWebsitePrefsData;
use App\Data\UserData;
use App\Data\UserResettableGameAchievementData;
use App\Data\UserResettableGameData;
use App\Enums\Permissions;
use App\Http\Controller;
use App\Http\Requests\ResetConnectApiKeyRequest;
use App\Http\Requests\ResetPlayerProgressRequest;
use App\Http\Requests\ResetWebApiKeyRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateWebsitePrefsRequest;
use App\Http\Requests\UserResettableGameAchievementsRequest;
use App\Models\User;
use App\Platform\Actions\ResetPlayerProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SettingsController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('updateSettings');

        $user = UserData::fromUser(Auth::user())->include(
            'apiKey',
            'canUpdateAvatar',
            'deleteRequested',
            'emailAddress',
            'motto',
            'userWallActive',
            'websitePrefs',
        );

        return Inertia::render('settings', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $section = 'profile'): View
    {
        $this->authorize('updateSettings', $section);

        if (!view()->exists("settings.$section")) {
            abort(404, 'Not found');
        }

        return view("settings.$section");
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $data = UpdatePasswordData::fromRequest($request);

        /** @var User $user */
        $user = $request->user();

        changePassword($user->username, $data->newPassword);
        generateAppToken($user->username, $tokenInOut);

        return response()->json(['success' => true]);
    }

    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        $data = UpdateEmailData::fromRequest($request);

        /** @var User $user */
        $user = $request->user();

        // The user will need to reconfirm their email address.
        $user->EmailAddress = $data->newEmail;
        $user->Permissions = Permissions::Unregistered;
        $user->email_verified_at = null;
        $user->save();

        sendValidationEmail($user->username, $data->newEmail);

        addArticleComment(
            'Server',
            ArticleType::UserModeration,
            $user->id,
            $user->username . ' changed their email address'
        );

        return response()->json(['success' => true]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $data = UpdateProfileData::fromRequest($request);

        /** @var User $user */
        $user = $request->user();
        $user->update($data->toArray());

        return response()->json(['success' => true]);
    }

    public function updatePreferences(UpdateWebsitePrefsRequest $request): JsonResponse
    {
        $data = UpdateWebsitePrefsData::fromRequest($request);

        /** @var User $user */
        $user = $request->user();
        $user->update($data->toArray());

        return response()->json(['success' => true]);
    }

    public function resetWebApiKey(ResetWebApiKeyRequest $request): JsonResponse
    {
        $newKey = generateAPIKey($request->user()->username);

        return response()->json(['newKey' => $newKey]);
    }

    public function resetConnectApiKey(ResetConnectApiKeyRequest $request): JsonResponse
    {
        generateAppToken($request->user()->username, $newToken);

        return response()->json(['success' => true]);
    }

    public function resetProgress(ResetPlayerProgressRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(['message' => __('legacy.success.reset')]);

        $action = app()->make(ResetPlayerProgress::class);

        if ($request->achievementId) {
            $action->execute($user, achievementID: (int) $request->achievementId);

            return response()->json(['message' => __('legacy.success.reset')]);
        }

        if ($request->gameId) {
            $action->execute($user, gameID: (int) $request->gameId);

            return response()->json(['message' => __('legacy.success.reset')]);
        }
    }

    public function getUserResettableGames(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $resettableGames = $user
            ->games()
            ->with('system')
            ->where('player_games.achievements_unlocked', '>', 0)
            ->orderBy('Title')
            ->select(['GameData.ID', 'Title', 'ConsoleID', 'achievements_published', 'player_games.achievements_unlocked'])
            ->get()
            ->map(function ($game) {
                return new UserResettableGameData(
                    id: $game->id,
                    title: $game->title,
                    consoleName: $game->system->name,
                    numAwarded: $game->achievements_unlocked,
                    numPossible: $game->achievements_published
                );
            });

        return response()->json(['results' => $resettableGames]);
    }

    public function getUserResettableGameAchievements(UserResettableGameAchievementsRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $gameId = $request->gameId;

        $resettableGameAchievements = $user
            ->achievements()
            ->where('GameID', $gameId)
            ->withPivot(['unlocked_at', 'unlocked_hardcore_at'])
            ->orderBy('Title')
            ->get()
            ->map(function ($unlockedAchievement) {
                return new UserResettableGameAchievementData(
                    id: $unlockedAchievement->id,
                    title: $unlockedAchievement->title,
                    points: $unlockedAchievement->points,
                    isHardcore: $unlockedAchievement->pivot->unlocked_hardcore_at ? true : false,
                );
            });

        return response()->json(['results' => $resettableGameAchievements]);
    }
}
