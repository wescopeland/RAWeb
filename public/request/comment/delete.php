<?php

use App\Enums\Permissions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

if (!authenticateFromCookie($user, $permissions, Permissions::Registered)) {
    abort(401);
}

$input = Validator::validate(Arr::wrap(request()->post()), [
    'comment' => 'required|integer|exists:Comment,ID',
]);

if (RemoveComment((int) $input['comment'], $user->id, $permissions)) {
    return response()->json(['message' => __('legacy.success.delete')]);
}

abort(400);
