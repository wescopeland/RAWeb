<?php

use App\Community\Enums\ArticleType;
use App\Enums\Permissions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

if (!authenticateFromCookie($user, $permissions, Permissions::Developer)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(Arr::wrap(request()->post()), [
    'leaderboard' => 'required|integer|exists:LeaderboardDef,ID',
]);

$lbId = (int) $input['leaderboard'];

requestResetLB($lbId);

addArticleComment(
    "Server",
    ArticleType::Leaderboard,
    $lbId,
    "{$user->display_name} reset all entries for this leaderboard.",
    $user->username,
);

return back()->with('success', __('legacy.success.ok'));
