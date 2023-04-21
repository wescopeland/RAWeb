<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LegacyApp\Community\Enums\ArticleType;
use LegacyApp\Community\Enums\ClaimSetType;
use LegacyApp\Community\Enums\ClaimSpecial;
use LegacyApp\Community\Enums\ClaimType;
use LegacyApp\Site\Enums\Permissions;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::JuniorDeveloper)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(Arr::wrap(request()->post()), [
    'game' => 'required|integer|exists:mysql_legacy.GameData,ID',
    'claim_type' => ['required', 'integer', Rule::in(ClaimType::cases())],
    'set_type' => ['required', 'integer', Rule::in(ClaimSetType::cases())],
    'create_topic' => 'sometimes|boolean',
]);

$gameID = (int) $input['game'];
$claimType = (int) $input['claim_type'];
$setType = (int) $input['set_type'];
$createForumTopic = (bool) ($input['create_topic'] ?? false);

$special = (int) checkIfSoleDeveloper($user, $gameID);

function isJuniorDeveloperAllowedToClaimSubset(string $user, array $gameData, array $subsetGameTitleMatches): bool {
    if (!empty($subsetGameTitleMatches)) {
        $parentGameTitle = trim($subsetGameTitleMatches[1]);
        $parentID = getGameIDFromTitle($parentGameTitle, $gameData['ConsoleID']);

        // Does the Jr Dev have a claim on the parent title?
        $claimData = getClaimData($parentID, true);
        foreach ($claimData as $claim) {
            if (isset($claim['User']) && $claim['User'] === $user) {
                return true;
            }
        }
    }

    return false;
}

// If the user is a Junior Developer, they shouldn't be allowed to exceed the Jr Dev claims limit, including for collaborations.
// The only exception to this rule is if they are claiming a subset of their already-active set.
if ($permissions === Permissions::JuniorDeveloper) {
    $totalActiveClaimCount = getActiveClaimCount($user, true, true); // 1
    $gameData = getGameData($gameID);
    $isTryingToClaimSubset = preg_match('/(.+)(\[Subset - .+\])/', $gameData['Title'], $subsetGameTitleMatches); // true
    $canJrClaimSubset = isJuniorDeveloperAllowedToClaimSubset($user, $gameData, $subsetGameTitleMatches); // true

    if (
        ($totalActiveClaimCount >= permissionsToClaim($permissions) && !$isTryingToClaimSubset) ||
        ($isTryingToClaimSubset && !$canJrClaimSubset)
    ) {
        return back()->withErrors(__('legacy.error.permissions'));
    }

    if ($isTryingToClaimSubset) {
        $special = ClaimSpecial::OwnRevision;
    }
}

if (insertClaim($user, $gameID, $claimType, $setType, $special, (int) $permissions)) {
    addArticleComment("Server", ArticleType::SetClaim, $gameID, ClaimType::toString($claimType) . " " . ($setType == ClaimSetType::Revision ? "revision" : "") . " claim made by " . $user);

    if ($createForumTopic && $permissions >= Permissions::Developer) {
        generateGameForumTopic($user, $gameID, $forumTopicID);

        return redirect(route('game.show', $gameID));
    }

    return back()->with('success', __('legacy.success.ok'));
}

return back()->withErrors(__('legacy.error.error'));
