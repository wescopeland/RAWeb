<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controller;
use App\Http\Requests\UpdateForumPostPermissionsRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UserApiController extends Controller
{
    public function updateForumPostPermissions(
        UpdateForumPostPermissionsRequest $request,
    ): JsonResponse {
        Gate::authorize('manage', User::class);

        $sourceUser = $request->user();
        $targetUser = User::whereName($request->input('displayName'))->first();

        setAccountForumPostAuth(
            $sourceUser,
            (int) $sourceUser->getAttribute('Permissions'),
            $targetUser,
            authorize: $request->input('isAuthorized')
        );

        return response()->json(['success' => true]);
    }
}
