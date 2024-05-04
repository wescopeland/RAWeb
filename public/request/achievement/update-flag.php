<?php

use App\Community\Enums\ArticleType;
use App\Enums\Permissions;
use App\Platform\Enums\AchievementFlag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

if (!authenticateFromCookie($user, $permissions, Permissions::Developer)) {
    abort(401);
}

$input = Validator::validate(Arr::wrap(request()->post()), [
    'achievements' => 'required',
    'flag' => ['required', 'integer', Rule::in(AchievementFlag::cases())],
]);

$achievementIds = $input['achievements'];
$value = (int) $input['flag'];

$achievement = GetAchievementData((int) (is_array($achievementIds) ? $achievementIds[0] : $achievementIds));
if ($value === AchievementFlag::OfficialCore && !isValidConsoleId($achievement['ConsoleID'])) {
    abort(400, 'Invalid console');
}

updateAchievementFlag($achievementIds, $value);

$commentText = '';
if ($value == AchievementFlag::OfficialCore) {
    $commentText = 'promoted this achievement to the Core set';
}
if ($value == AchievementFlag::Unofficial) {
    $commentText = 'demoted this achievement to Unofficial';
}
addArticleComment("Server", ArticleType::Achievement, $achievementIds, "{$user->display_name} $commentText.", $user->username);
expireGameTopAchievers($achievement['GameID']);

return response()->json(['message' => __('legacy.success.ok')]);
