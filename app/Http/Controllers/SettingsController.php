<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\ProfileSettingsData;
use App\Data\UserData;
use App\Data\WebsitePrefsData;
use App\Http\Controller;
use App\Http\Requests\ProfileSettingsRequest;
use App\Http\Requests\WebsitePrefsRequest;
use App\Models\User;
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

    public function updateProfile(ProfileSettingsRequest $request): JsonResponse
    {
        $data = ProfileSettingsData::fromRequest($request);

        /** @var User $user */
        $user = $request->user();
        $user->update($data->toArray());

        return response()->json(['success' => true]);
    }

    public function updatePreferences(WebsitePrefsRequest $request): JsonResponse
    {
        $data = WebsitePrefsData::fromRequest($request);

        /** @var User $user */
        $user = $request->user();
        $user->update($data->toArray());

        return response()->json(['success' => true]);
    }
}
