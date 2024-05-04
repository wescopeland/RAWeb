<?php

use App\Community\Enums\AwardType;
use App\Models\PlayerBadge;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

if (!authenticateFromCookie($user, $permissions)) {
    abort(401);
}

Validator::extend('awards_csv_format', function ($attribute, $value) {
    $pattern = '/^(\d+,\d+,\d+,-?\d+)(\|\d+,\d+,\d+,-?\d+)*$/';

    return preg_match($pattern, $value);
}, 'The :attribute must contain groups of 4 comma-separated integers, with the 4th value allowing a negative integer.');

$input = Validator::validate(Arr::wrap(request()->post()), [
    'hiddenAwards' => ['nullable', 'string', 'awards_csv_format'],
    'sortedAwards' => ['nullable', 'string', 'awards_csv_format'],
]);

$hiddenCsv = $input['hiddenAwards'];
$sortedCsv = $input['sortedAwards'];

// The awards this endpoint receives are compressed to save on variable space.
// This function is responsible for decompressing/parsing the awards.
$parseCsv = function ($csv) {
    if (empty($csv)) {
        return [];
    }

    $awardStrings = explode('|', $csv);
    $awards = [];

    foreach ($awardStrings as $awardString) {
        $decoded = explode(',', $awardString);

        $awards[] = [
            'type' => intval($decoded[0]),
            'data' => intval($decoded[1]),
            'extra' => intval($decoded[2]),
            'number' => intval($decoded[3]),
        ];
    }

    return $awards;
};

$hiddenAwards = $parseCsv($hiddenCsv);
$sortedAwards = $parseCsv($sortedCsv);

$awards = array_merge($hiddenAwards, $sortedAwards);

foreach ($awards as $award) {
    $awardType = $award['type'];
    $awardData = $award['data'];
    $awardDataExtra = $award['extra'];
    $value = $award['number'];

    $query = PlayerBadge::where('user_id', $user->id)
        ->where('AwardType', $awardType);

    // Change display order for all entries if it's a "stacking" award type.
    if (in_array($awardType, [AwardType::AchievementUnlocksYield, AwardType::AchievementPointsYield])) {
        $query->where('AwardDataExtra', $awardDataExtra);
    } else {
        $query->where('AwardData', $awardData);
    }

    $query->update(['DisplayOrder' => $value]);
}

$userAwards = getUsersSiteAwards($user->username);
$updatedAwardsHTML = '';
ob_start();
RenderSiteAwards($userAwards, $user->username);
$updatedAwardsHTML = ob_get_clean();

return response()->json([
    'message' => __('legacy.success.ok'),
    'updatedAwardsHTML' => $updatedAwardsHTML,
]);
