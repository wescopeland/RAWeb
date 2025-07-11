<?php

use App\Community\Enums\ArticleType;
use App\Community\Enums\AwardType;
use App\Community\Enums\ClaimSetType;
use App\Community\Enums\ClaimStatus;
use App\Community\Enums\ClaimType;
use App\Community\Enums\SubscriptionSubjectType;
use App\Community\Enums\UserGameListType;
use App\Enums\Permissions;
use App\Enums\UserPreference;
use App\Models\EventAchievement;
use App\Models\Game;
use App\Models\GameSet;
use App\Models\PlayerBadge;
use App\Models\System;
use App\Models\User;
use App\Models\UserGameListEntry;
use App\Platform\Enums\AchievementFlag;
use App\Platform\Enums\AchievementType;
use App\Platform\Enums\GameSetType;
use App\Platform\Enums\ImageType;
use App\Platform\Enums\UnlockMode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

$gameID = (int) request('game');
if (empty($gameID)) {
    abort(404);
}

authenticateFromCookie($user, $permissions, $userDetails);

$userID = $userDetails['ID'] ?? 0;
$userWebsitePrefs = $userDetails['websitePrefs'] ?? null;
$matureContentPref = UserPreference::Site_SuppressMatureContentWarning;

$officialFlag = AchievementFlag::OfficialCore;
$unofficialFlag = AchievementFlag::Unofficial;
$flagParam = AchievementFlag::tryFrom(
    requestInputSanitized('f', AchievementFlag::OfficialCore->value, 'integer')
) ?? AchievementFlag::OfficialCore;

$isOfficial = false;
if ($flagParam !== AchievementFlag::Unofficial) {
   $isOfficial = true;
   $flagParam = AchievementFlag::OfficialCore;
}

$userModel = null;
$defaultSort = 1;
if (isset($user)) {
    $userModel = User::find($userID);
    $defaultSort = 13;
}
$sortBy = requestInputSanitized('s', $defaultSort, 'integer');

if (!isset($user) && ($sortBy == 3 || $sortBy == 13)) {
    $sortBy = 1;
}

$numAchievements = getGameMetadata($gameID, $userModel, $achievementData, $gameData, $sortBy, null, $flagParam, metrics: true);
$gameModel = Game::with(['hubs.viewRoles'])->find($gameID);

if (!$gameModel) {
    abort(404);
}

$mapGameToAlt = fn (Game $game) => [
    'gameIDAlt' => $game->id,
    'Title' => $game->title,
    'ImageIcon' => $game->ImageIcon,
    'ConsoleName' => $game->system->name,
    'Points' => $game->points_total,
    'TotalTruePoints' => $game->points_weighted,
    'isFullyFeaturedGame' => true,
];

$mapGameHubToAlt = fn (GameSet $gameSet) => [
    'gameIDAlt' => $gameSet->game_id,
    'GameSetID' => $gameSet->id,
    'Title' => $gameSet->title,
    'ImageIcon' => $gameSet->image_asset_path,
    'ConsoleName' => 'Hubs',
    'Points' => 0,
    'TotalTruePoints' => 0,
    'isFullyFeaturedGame' => false,
];

// Redirect legacy hubs to the dedicated Inertia.js hub page.
if ($gameModel->ConsoleID === System::Hubs) {
    $foundGameSet = GameSet::whereType(GameSetType::Hub)
        ->whereGameId($gameModel->id)
        ->first();

    if ($foundGameSet) {
        abort_with(redirect(route('hub.show', ['gameSet' => $foundGameSet])));
    } else {
        abort(404);
    }
}

$gameTitle = $gameData['Title'];
$consoleName = $gameData['ConsoleName'];
$consoleID = $gameData['ConsoleID'];
$forumTopicID = $gameData['ForumTopicID'];
$richPresenceData = $gameData['RichPresencePatch'];
$guideURL = $gameData['GuideURL'];

// Entries that aren't actual game only have alternatives exposed, e.g. hubs.
$isFullyFeaturedGame = System::isGameSystem($gameData['ConsoleID']);
$isEventGame = $gameData['ConsoleID'] == System::Events;

if ($isEventGame && $gameModel->event) {
    abort_with(redirect(route('event.show', ['event' => $gameModel->event])));
}

$pageTitle = "$gameTitle ($consoleName)";

$unlockedAchievements = array_filter($achievementData, function ($achievement) {
    return !empty($achievement['DateEarned']) || !empty($achievement['DateEarnedHardcore']);
});
$beatenGameCreditDialogContext = buildBeatenGameCreditDialogContext($unlockedAchievements);

$allSimilarGames = $gameModel->similarGamesList;
$allGameHubSets = $gameModel->hubs;

// Filter hubs based on user's view permissions.
$visibleHubs = $allGameHubSets->filter(function ($hub) use ($userModel) {
    // If the user is a guest, only show hubs without view restrictions.
    if (!$userModel) {
        return !$hub->has_view_role_requirement;
    }

    return $userModel->can('view', $hub);
});

$gameHubs = $visibleHubs->map($mapGameHubToAlt)->values()->sortBy('Title')->all();

$v = requestInputSanitized('v', 0, 'integer');
$gate = false;
if ($v != 1) {
    $canBypassGate = $userDetails && BitSet($userDetails['websitePrefs'], $matureContentPref);
    if (!$canBypassGate) {
        $gate = $gameModel->has_mature_content;
    }
}
?>

<?php
$softcoreUnlocks = null;
$hardcoreUnlocks = null;
$authorInfo = [];
$lbData = null;
$numDistinctPlayers = null;
$numEarnedCasual = null;
$numEarnedHardcore = null;
$screenshotMaxHeight = null;
$screenshotWidth = null;
$totalEarnedCasual = null;
$totalEarnedHardcore = null;
$totalEarnedTrueRatio = null;
$totalPossible = null;
$totalPossibleTrueRatio = null;
$isSoleAuthor = false;
$claimData = null;
$isGameBeatable = false;
$isBeatenHardcore = false;
$isBeatenSoftcore = false;
$hasBeatenHardcoreAward = false;
$hasBeatenSoftcoreAward = false;
$userGameProgressionAwards = [
    'beaten-softcore' => null,
    'beaten-hardcore' => null,
    'completed' => null,
    'mastered' => null,
];

if ($isFullyFeaturedGame || $isEventGame) {
    $numDistinctPlayers = $gameData['NumDistinctPlayers'];

    $softcoreUnlocks = getAchievementDistribution($gameID, UnlockMode::Softcore, $user, $flagParam, $numDistinctPlayers);
    $hardcoreUnlocks = getAchievementDistribution($gameID, UnlockMode::Hardcore, $user, $flagParam, $numDistinctPlayers);

    if (isset($user)) {
        // Determine if the logged in user is the sole author of the set
        $isSoleAuthor = checkIfSoleDeveloper($userModel, $gameID);

        // Determine if the logged in user has any progression awards for this set
        $userGameProgressionAwards = getUserGameProgressionAwards($gameID, $userModel);
        $hasBeatenSoftcoreAward = !is_null($userGameProgressionAwards['beaten-hardcore']);
        $hasBeatenHardcoreAward = !is_null($userGameProgressionAwards['beaten-softcore']);
    }

    $screenshotWidth = 200;
    $screenshotMaxHeight = 240; // corresponds to the DS screen aspect ratio

    // Quickly calculate earned/potential
    $totalEarnedCasual = 0;
    $totalEarnedHardcore = 0;
    $numEarnedCasual = 0;
    $numEarnedHardcore = 0;
    $totalPossible = 0;

    // Quickly calculate the player's beaten status on an optimistic basis
    $totalProgressionAchievements = 0;
    $totalWinConditionAchievements = 0;
    $totalEarnedProgression = 0;
    $totalEarnedProgressionHardcore = 0;
    $totalEarnedWinCondition = 0;
    $totalEarnedWinConditionHardcore = 0;

    $totalEarnedTrueRatio = 0;
    $totalPossibleTrueRatio = 0;

    $authorName = [];
    $authorCount = [];
    if (isset($achievementData)) {
        foreach ($achievementData as &$nextAch) {
            $lowercasedAuthor = mb_strtolower($nextAch['Author']);

            // Add author to array if it's not already there and initialize achievement count for that author.
            if (!in_array($nextAch['Author'], $authorName)) {
                $authorName[$lowercasedAuthor] = $nextAch['Author'];
                $authorCount[$lowercasedAuthor] = 1;
            } // If author is already in array then increment the achievement count for that author.
            else {
                $authorCount[$lowercasedAuthor]++;
            }

            $totalPossible += $nextAch['Points'];
            $totalPossibleTrueRatio += $nextAch['TrueRatio'];

            // Tally up how many Progression and Win Condition achievements the user has earned.
            // We'll use this to determine if they're potentially missing a beaten game award.
            if (
                isset($nextAch['type'])
                && ($nextAch['type'] == AchievementType::Progression || $nextAch['type'] == AchievementType::WinCondition)
            ) {
                $isGameBeatable = true;

                if ($nextAch['type'] == AchievementType::Progression) {
                    $totalProgressionAchievements++;
                    if (isset($nextAch['DateEarned'])) {
                        $totalEarnedProgression++;
                    }
                    if (isset($nextAch['DateEarnedHardcore'])) {
                        $totalEarnedProgressionHardcore++;
                    }
                } elseif ($nextAch['type'] == AchievementType::WinCondition) {
                    $totalWinConditionAchievements++;
                    if (isset($nextAch['DateEarned'])) {
                        $totalEarnedWinCondition++;
                    }
                    if (isset($nextAch['DateEarnedHardcore'])) {
                        $totalEarnedWinConditionHardcore++;
                    }
                }
            }

            if (isset($nextAch['DateEarnedHardcore'])) {
                $numEarnedHardcore++;
                $totalEarnedHardcore += $nextAch['Points'];
                $totalEarnedTrueRatio += $nextAch['TrueRatio'];
            } elseif (isset($nextAch['DateEarned'])) {
                $numEarnedCasual++;
                $totalEarnedCasual += $nextAch['Points'];
            }
        }
        // Combine arrays and sort by achievement count.
        $authorInfo = array_combine($authorName, $authorCount);
        array_multisort($authorCount, SORT_DESC, $authorInfo);
    }

    // Show the beaten award display in the progress component optimistically.
    // The actual award metadata is updated async via actions/background jobs.
    if ($user && $isGameBeatable && !$isEventGame) {
        $neededProgressions = $totalProgressionAchievements > 0 ? $totalProgressionAchievements : 0;
        $neededWinConditions = $totalWinConditionAchievements > 0 ? 1 : 0;

        $isBeatenSoftcore = (
            $totalEarnedProgression === $totalProgressionAchievements
            && $totalEarnedWinCondition >= $neededWinConditions
        );

        $isBeatenHardcore = (
            $totalEarnedProgressionHardcore === $totalProgressionAchievements
            && $totalEarnedWinConditionHardcore >= $neededWinConditions
        );
    }

    $claimData = getClaimData([$gameID], true);
}

if ($isEventGame) {
    $eventAchievements = EventAchievement::whereIn('achievement_id', array_keys($achievementData))->with('sourceAchievement.game')->get();
    foreach ($eventAchievements as $eventAchievement) {
        $achievementData[$eventAchievement->achievement_id]['SourceAchievementId'] = $eventAchievement->source_achievement_id;
        $achievementData[$eventAchievement->achievement_id]['SourceGameId'] = $eventAchievement->sourceAchievement?->game->id;
        $achievementData[$eventAchievement->achievement_id]['SourceGameTitle'] = $eventAchievement->sourceAchievement?->game->title;
        $achievementData[$eventAchievement->achievement_id]['ActiveFrom'] = $eventAchievement->active_from ?? null;
        $achievementData[$eventAchievement->achievement_id]['ActiveUntil'] = $eventAchievement->active_until?->subSeconds(1);
    }

    // hide points unless more than 1. never show TrueRatio.
    foreach ($achievementData as &$achievement) {
        if ($achievement['Points'] === 1) {
            $achievement['Points'] = 0;
        }
        $achievement['TrueRatio'] = 0;
    }
    $totalEarnedTrueRatio = 0;

    $isGameBeatable = true;

    if ($userModel) {
        $isBeatenHardcore = PlayerBadge::where('user_id', $userModel->id)
            ->where('AwardType', AwardType::Mastery)
            ->where('AwardData', $gameModel->id)
            ->exists();
    }
}

sanitize_outputs(
    $gameTitle,
    $consoleName,
    $richPresenceData,
    $user,
);

$pageType = $isFullyFeaturedGame ? 'retroachievements:game' : 'retroachievements:hub';
$pageImage = media_asset($gameData['ImageIcon']);

if ($isFullyFeaturedGame) {
    $pageDescription = generateGameMetaDescription(
            $gameTitle,
            $consoleName,
            $numAchievements,
            $totalPossible,
            $isEventGame
        );
}

?>

@if ($gate)
    <x-app-layout
        :pageTitle="$pageTitle"
        :pageDescription="$pageDescription ?? null"
        :pageImage="$pageImage ?? null"
        :pageType="$pageType ?? null"
    >
        <x-game.mature-content-gate
            :gameId="$gameID"
            :gameTitle="$gameTitle"
            :consoleId="$consoleID"
            :consoleName="$consoleName"
            :userWebsitePrefs="$userDetails['websitePrefs'] ?? 0"
        />
    </x-app-layout>
    <?php return ?>
@endif

<x-app-layout
    :pageTitle="$pageTitle"
    :pageDescription="$pageDescription ?? null"
    :pageImage="$pageImage ?? null"
    :pageType="$pageType ?? null"
>
<?php if ($isFullyFeaturedGame || $isEventGame): ?>
    <?php if ($numDistinctPlayers): ?>
        <script defer src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof google !== 'undefined') {
                google.load('visualization', '1.0', { 'packages': ['corechart'] });
                google.setOnLoadCallback(drawCharts);
            }
        });

        function drawCharts() {
            var dataTotalScore = new google.visualization.DataTable();

            // Declare columns
            dataTotalScore.addColumn('number', 'Total Achievements Won');
            dataTotalScore.addColumn('number', 'Hardcore Users');
            dataTotalScore.addColumn('number', 'Softcore Users');

            dataTotalScore.addRows([
                <?php
                [$buckets, $isDynamicBucketingEnabled] = generateEmptyBucketsWithBounds($numAchievements);
                [$largestWonByCount] = calculateBuckets($buckets, $isDynamicBucketingEnabled, $numAchievements, $softcoreUnlocks, $hardcoreUnlocks);
                $allAchievementsCount = handleAllAchievementsCase($numAchievements, $softcoreUnlocks, $hardcoreUnlocks, $buckets);
                $largestWonByCount = max($allAchievementsCount, $largestWonByCount);

                $numGridlines = ($numAchievements < 20) ? $numAchievements : 10;
                if ($largestWonByCount > 20) {
                    $largestWonByCount = -2;
                }

                $hAxisValues = generateBucketLabelsAndValues($numAchievements, $buckets);
                ?>
            ]);
            var hAxisValues = <?php echo json_encode($hAxisValues); ?>;
            var optionsTotalScore = {
                isStacked: true,
                backgroundColor: 'transparent',
                titleTextStyle: { color: '#186DEE' },
                hAxis: {
                    textStyle: { color: '#186DEE' },
                    gridlines: {
                        count: <?= $numGridlines ?>,
                        color: '#333333'
                    },
                    <?php
                    if ($isDynamicBucketingEnabled) {
                        echo 'ticks: hAxisValues.map(function(value, index) { return {v: index + 1, f: value.toString()}; }),';
                    }
                    ?>
                    minorGridlines: { count: 0 },
                    format: '#',
                    slantedTextAngle: 90,
                    maxAlternation: 0,
                },
                vAxis: {
                    textStyle: { color: '#186DEE' },
                    gridlines: {
                        count: <?= $largestWonByCount + 1 ?>,
                        color: '#333333'
                    },
                    minorGridlines: { color: '#333333' },
                    viewWindow: { min: 0 },
                    format: '#'
                },
                legend: { position: 'none' },
                chartArea: {
                    'width': '80%',
                    'height': '78%'
                },
                height: 260,
                colors: ['#cc9900', '#737373'],
                pointSize: 4,
            };

            function resize() {
                chartScoreProgress = new google.visualization.ColumnChart(document.getElementById('chart_distribution'));
                chartScoreProgress.draw(dataTotalScore, optionsTotalScore);
                // google.visualization.events.addListener(chartScoreProgress, 'select', selectHandlerScoreProgress );
            }

            window.onload = resize();
            window.onresize = resize;
        }
        </script>
    <?php endif ?>
<?php endif ?>
    <div id="achievement">
        <?php

        if ($isFullyFeaturedGame) {
            echo "<div class='navpath'>";
            echo renderGameBreadcrumb($gameData, addLinkToLastCrumb: $flagParam === $unofficialFlag);
            if ($flagParam === $unofficialFlag) {
                echo " &raquo; <b>Unofficial Achievements</b>";
            }
            echo "</div>";
        }

        $escapedGameTitle = attributeEscape($gameTitle);
        $consoleName = $gameData['ConsoleName'] ?? null;
        $developer = $gameData['Developer'] ?? null;
        $publisher = $gameData['Publisher'] ?? null;
        $genre = $gameData['Genre'] ?? null;
        $imageIcon = media_asset($gameData['ImageIcon']);
        $imageTitle = media_asset($gameData['ImageTitle']);
        $imageIngame = media_asset($gameData['ImageIngame']);
        $pageTitleAttr = attributeEscape($pageTitle);

        $systemIconUrl = getSystemIconUrl($consoleID);

        $numMissableAchievements = count(
            array_filter(
                $achievementData,
                fn ($achievement) => $achievement['type'] === AchievementType::Missable
            ));

        $gameMetaBindings = [
            'claimData' => $claimData,
            'consoleID' => $consoleID,
            'consoleName' => $consoleName,
            'developer' => $developer,
            'forumTopicID' => $forumTopicID,
            'gameHubs' => $gameHubs,
            'gameID' => $gameID,
            'gameTitle' => $gameTitle,
            'genre' => $genre,
            'iconUrl' => $systemIconUrl,
            'imageIcon' => $imageIcon,
            'isFullyFeaturedGame' => $isFullyFeaturedGame,
            'isOfficial' => $isOfficial,
            'isSoleAuthor' => $isSoleAuthor,
            'numAchievements' => $numAchievements,
            'numMissableAchievements' => $numMissableAchievements,
            'permissions' => $permissions,
            'publisher' => $publisher,
            'totalPossible' => $totalPossible,
            'totalPossibleTrueRatio' => $totalPossibleTrueRatio,
            'user' => $user,
            'userModel' => $userModel,
        ];
        ?>
            <x-game.heading
                :gameId="$gameID"
                :gameTitle="$gameTitle"
                :consoleId="$consoleID"
                :consoleName="$consoleName"
                :includeAddToListButton="true"
            />
            <x-game.primary-meta
                :imageIcon="$imageIcon"
                :metaKind="$isFullyFeaturedGame ? 'Game' : 'Hub'"
            >
                @if ($isFullyFeaturedGame)
                    <x-game.primary-meta-row-item label="Developer" :metadataValue="$developer" :gameHubs="$gameHubs" :altLabels="['Hacker']" />
                    <x-game.primary-meta-row-item label="Publisher" :metadataValue="$publisher" :gameHubs="$gameHubs" :altLabels="['Hacks']" />
                    <x-game.primary-meta-row-item label="Genre" :metadataValue="$genre" :gameHubs="$gameHubs" :altLabels="['Subgenre']" />
                @else
                    <x-game.primary-meta-row-item label="Developer" :metadataValue="$developer" />
                    <x-game.primary-meta-row-item label="Publisher" :metadataValue="$publisher" />
                    <x-game.primary-meta-row-item label="Genre" :metadataValue="$genre" />
                @endif

                @php
                    $releasedAtDisplay = null;

                    if ($gameModel->released_at && $gameModel->released_at_granularity) {
                        $releasedAtDisplay = match ($gameModel->released_at_granularity->value) {
                            'year' => $gameModel->released_at->format('Y'),
                            'month' => $gameModel->released_at->format('F Y'),
                            default => $gameModel->released_at->format('F j, Y'),
                        };
                    }
                @endphp
                <x-game.primary-meta-row-item label="Released" :metadataValue="$releasedAtDisplay" />
            </x-game.primary-meta>

        @if ($isFullyFeaturedGame || $isEventGame)
            <x-game.screenshots :titleImageSrc="$imageTitle" :ingameImageSrc="$imageIngame" />
        @endif

        @if ($isFullyFeaturedGame || ($isEventGame))
            @if ($userModel && $userModel->can('update', $gameModel))
                <a class="btn mb-1" href="{{ route('filament.admin.resources.games.edit', ['record' => $gameModel->id]) }}">Manage</a>
            @elseif ($userModel && $userModel->can('manage', $gameModel))
                <a class="btn mb-1" href="{{ route('filament.admin.resources.games.view', ['record' => $gameModel->id]) }}">Manage</a>
            @endif
        @endif

        <?php
        // Display dev section if logged in as either a developer or a jr. developer viewing a non-hub page
        // TODO migrate devbox entirely to filament
        if (isset($user) && ($permissions >= Permissions::Developer || ($isFullyFeaturedGame && $permissions >= Permissions::JuniorDeveloper)) && (!$isEventGame)) {
            // TODO use a policy
            $hasMinimumDeveloperPermissions = (
                $permissions >= Permissions::Developer
                || (
                    ($isSoleAuthor || hasSetClaimed($userModel, $gameID, true, ClaimSetType::NewSet))
                    && $permissions >= Permissions::JuniorDeveloper
                )
            );

            echo "<div class='devbox mb-3'>";
            echo "<span onclick=\"$('#devboxcontent').toggle(); return false;\">Dev ▼</span>";
            echo "<div id='devboxcontent' style='display: none'>";
            // Display the option to switch between viewing core/unofficial for non-hub page
            if ($isFullyFeaturedGame) {
                echo "<div class='lg:flex justify-between gap-5 mb-5'>";
                echo "<div class='grow'>";

                if ($flagParam === $unofficialFlag) {
                    echo "<div><a class='btn btn-link' href='/game/$gameID" . ($v == 1 ? '?v=1' : '') . "'>View Core Achievements</a></div>";
                    echo "<div><a class='btn btn-link' href='/achievementinspector.php?g=$gameID&f=5'>Manage Unofficial Achievements</a></div>";
                } else {
                    echo "<div><a class='btn btn-link' href='/game/$gameID?f=5" . ($v == 1 ? '&v=1' : '') . "'>View Unofficial Achievements</a></div>";
                    echo "<div><a class='btn btn-link' href='/achievementinspector.php?g=$gameID'>Manage Core Achievements</a></div>";
                }

                // Display leaderboard management options depending on if the game has any leaderboards (including hidden)
                if ($gameModel->leaderboards()->exists()) {
                    $manageLeaderboardsRoute = route('filament.admin.resources.leaderboards.index', [
                        'tableFilters[game][id]' => $gameID,
                        'tableSortColumn' => 'DisplayOrder',
                        'tableSortDirection' => 'asc',
                    ]);
                    echo "<div><a class='btn btn-link' href='$manageLeaderboardsRoute'>Manage Leaderboards</a></div>";
                }

                if ($permissions >= Permissions::Developer) {
                    $manageHashesHref = route('filament.admin.resources.games.hashes', ['record' => $gameID]);
                    echo "<div><a class='btn btn-link' href='{$manageHashesHref}'>Manage Hashes</a></div>";
                }

                $primaryClaimUser = null;
                foreach ($claimData as $claim) {
                    if ($claimData[0]['ClaimType'] == ClaimType::Primary) {
                        $primaryClaimUser = $claimData[0]['User'];
                        break;
                    }
                }
                if ($permissions >= Permissions::Moderator || $primaryClaimUser === $user) {
                    $interestedUsers = UserGameListEntry::where('type', UserGameListType::Develop)
                        ->where('GameID', $gameID)
                        ->count();
                    echo "<div><a class='btn btn-link' href='" . route('game.dev-interest', ['game' => $gameID]) . "'>View Developer Interest ($interestedUsers)</a></div>";
                }

                if ($permissions >= Permissions::Moderator && !$isEventGame) {
                    echo "<div><a class='btn btn-link' href='/manageclaims.php?g=$gameID'>Manage Claims</a></div>";
                }

                echo "</div>";
                // right column
                echo "<div class='grow'>";
                ?>

                <x-update-subscription-button
                    name="updateachievementssub"
                    subjectType="{{ SubscriptionSubjectType::GameAchievements }}"
                    subjectId="{{ $gameID }}"
                    isSubscribed="{{ isUserSubscribedTo(SubscriptionSubjectType::GameAchievements, $gameID, $userID) }}"
                    resource="Achievement Comments"
                />

                <x-update-subscription-button
                    name="updateticketssub"
                    subjectType="{{ SubscriptionSubjectType::GameTickets }}"
                    subjectId="{{ $gameID }}"
                    isSubscribed="{{ isUserSubscribedTo(SubscriptionSubjectType::GameTickets, $gameID, $userID) }}"
                    resource="Tickets"
                />

                {{-- Display the claims links if not an event game --}}
                @if (!$isEventGame)
                    @if ($permissions >= Permissions::Developer)
                        <livewire:game.add-to-list-button
                            label="Want to Develop"
                            :gameId="$gameID"
                            :listType="UserGameListType::Develop"
                        />
                    @endif
                    <x-game.devbox-claim-management
                        :claimData="$claimData"
                        :consoleId="$consoleID"
                        :forumTopicId="$forumTopicID"
                        :gameId="$gameID"
                        :gameTitle="$gameTitle"
                        :isOfficial="$isOfficial"
                        :isSoleAuthor="$isSoleAuthor"
                        :numAchievements="$numAchievements"
                        :user="$userModel"
                    />
                @endif
                <?php
                echo "</div>"; // end right column
                echo "</div>";
            }

            if ($hasMinimumDeveloperPermissions) {
                // Only allow developers to rename a game
                if ($permissions >= Permissions::Developer) {
                    echo "<form class='mb-2' method='post' action='/request/game/update-title.php'>";
                    echo csrf_field();
                    echo "<input type='hidden' name='game' value='$gameID' />";
                    echo "<div class='md:grid grid-cols-[180px_1fr_100px] gap-1 items-center mb-1'>";
                    echo "<label for='game_title'>Canonical Title</label>";
                    echo "<input type='text' name='title' id='game_title' value='$escapedGameTitle' maxlength='80' class='w-full'>";
                    echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                    echo "</div>";
                    echo "</form>";
                }

                echo "<form class='mb-2' method='post' action='/request/game/update-meta.php'>";
                echo csrf_field();
                echo "<input type='hidden' name='game' value='$gameID'>";
                echo "<input type='hidden' name='guide_url' value='" . attributeEscape($guideURL) . "'>";
                echo "<div class='md:grid grid-cols-[180px_1fr_100px] gap-1 items-center mb-1'>";
                echo "<label for='game_developer'>Developer</label><input type='text' name='developer' id='game_developer' value='" . attributeEscape($developer) . "' class='w-full'>";
                echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                echo "<label for='game_publisher'>Publisher</label><input type='text' name='publisher' id='game_publisher' value='" . attributeEscape($publisher) . "' class='w-full'>";
                echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                echo "<label for='game_genre'>Genre</label><input type='text' name='genre' id='game_genre' value='" . attributeEscape($genre) . "' class='w-full'>";
                echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                echo "</div>";
                echo "</form>";

                if ($isFullyFeaturedGame) {
                    echo "<form class='mb-2' method='post' action='/request/game/update-image.php' enctype='multipart/form-data'>";
                    echo csrf_field();
                    echo "<input type='hidden' name='game' value='$gameID'>";
                    echo "<input type='hidden' name='type' value='" . ImageType::GameTitle . "'>";
                    echo "<div class='md:grid grid-cols-[180px_1fr_100px] gap-1 items-center mb-1'>";
                    echo "<label for='image_" . ImageType::GameTitle . "'>Title Screenshot</label>";
                    echo "<input type='file' name='file' id='image_" . ImageType::GameTitle . "' class='w-full'>";
                    echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                    echo "</div>";
                    echo "</form>";

                    echo "<form class='mb-2' method='post' action='/request/game/update-image.php' enctype='multipart/form-data'>";
                    echo csrf_field();
                    echo "<input type='hidden' name='game' value='$gameID'>";
                    echo "<input type='hidden' name='type' value='" . ImageType::GameInGame . "'>";
                    echo "<div class='md:grid grid-cols-[180px_1fr_100px] gap-1 items-center mb-1'>";
                    echo "<label for='image_" . ImageType::GameInGame . "'>In-game Screenshot</label>";
                    echo "<input type='file' name='file' id='image_" . ImageType::GameInGame . "' class='w-full'>";
                    echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                    echo "</div>";
                    echo "</form>";
                }

                echo "<form class='mb-2' method='post' action='/request/game/update-image.php' enctype='multipart/form-data'>";
                echo csrf_field();
                echo "<input type='hidden' name='game' value='$gameID'>";
                echo "<input type='hidden' name='type' value='" . ImageType::GameIcon . "'>";
                echo "<div class='md:grid grid-cols-[180px_1fr_100px] gap-1 items-center mb-1'>";
                echo "<label for='image_" . ImageType::GameIcon . "'>Icon</label>";
                echo "<input type='file' name='file' id='image_" . ImageType::GameIcon . "' class='w-full'>";
                echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                echo "</div>";
                echo "</form>";

                if ($isFullyFeaturedGame) {
                    echo "<form class='mb-2' method='post' action='/request/game/update-image.php' enctype='multipart/form-data'>";
                    echo csrf_field();
                    echo "<input type='hidden' name='game' value='$gameID'>";
                    echo "<input type='hidden' name='type' value='" . ImageType::GameBoxArt . "'>";
                    echo "<div class='md:grid grid-cols-[180px_1fr_100px] gap-1 items-center mb-1'>";
                    echo "<label for='image_" . ImageType::GameBoxArt . "'>Box Art</label>";
                    echo "<input type='file' name='file' id='image_" . ImageType::GameBoxArt . "' class='w-full'>";
                    echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                    echo "</div>";
                    echo "</form>";
                }
            }

            if ($permissions >= Permissions::Moderator) {
                echo "<form class='mb-2' method='post' action='/request/game/update-forum-topic.php'>";
                echo csrf_field();
                echo "<input type='hidden' name='game' value='$gameID'>";
                echo "<div class='md:grid grid-cols-[180px_1fr_100px] gap-1 items-center mb-1'>";
                echo "<label for='game_forum_topic'>New Forum Topic ID</label>";
                echo "<input type='text' name='forum_topic' id='game_forum_topic' class='w-full'>";
                echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                echo "</div>";
                echo "</form>";
            }

            if ($hasMinimumDeveloperPermissions) {
                echo "<form class='mb-2' method='post' action='/request/game/update-meta.php'>";
                echo csrf_field();
                echo "<input type='hidden' name='game' value='$gameID'>";
                echo "<input type='hidden' name='developer' value='" . attributeEscape($developer) . "'>";
                echo "<input type='hidden' name='publisher' value='" . attributeEscape($publisher) . "'>";
                echo "<input type='hidden' name='genre' value='" . attributeEscape($genre) . "'>";
                echo "<div class='md:grid grid-cols-[180px_1fr_100px] gap-1 items-center mb-1'>";

                $guideUrlHelperContent = "Must be from https://github.com/RetroAchievements/guides";
                echo "<label for='guide_url' class='cursor-help flex items-center gap-x-1' title='$guideUrlHelperContent' aria-label='Guide URL, $guideUrlHelperContent'>";
                echo "Guide URL";
                ?>
                <x-fas-info-circle class="w-5 h-5" aria-hidden="true" />
                <?php
                echo "</label>";

                echo "<input type='url' name='guide_url' id='guide_url' value='" . attributeEscape($guideURL) . "' class='w-full'>";
                echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                echo "</div>";
                echo "</form>";
            }

            if ($userModel && $userModel->can('manage', [\App\Models\GameSet::class])) {
                $manageSimilarGamesHref = route('filament.admin.resources.games.similar-games', ['record' => $gameID]);
                $manageHubsHref = route('filament.admin.resources.games.hubs', ['record' => $gameID]);

                echo "<div class='mb-2 flex flex-col gap-2'>";
                echo "<a href='{$manageHubsHref}'>Manage Related Hubs</a>";
                echo "<a href='{$manageSimilarGamesHref}'>Manage Similar Games</a>";
                echo "</div>";
            }
            
            if ($isFullyFeaturedGame) {
                echo "<div><label for='game_rich_presence'><a href='https://docs.retroachievements.org/developer-docs/rich-presence.html'>Rich Presence</a> Script</label></div>";
                if ($hasMinimumDeveloperPermissions) {
                    echo "<form class='mb-2' method='post' action='/request/game/update-rich-presence.php'>";
                    echo csrf_field();
                    echo "<input type='hidden' value='$gameID' name='game'>";
                    echo "<textarea class='code w-full h-[320px] mb-1' name='rich_presence' id='game_rich_presence' maxlength='60000'>$richPresenceData</textarea><br>";
                    echo "<div class='text-right'><button class='btn'>Submit</button></div>";
                    echo "</form>";
                } else {
                    echo "<textarea class='code w-full h-[320px] mb-2' id='game_rich_presence' readonly>$richPresenceData</textarea>";
                }
            }

            echo Blade::render("<x-comment.list :articleType=\"\$articleType\" :articleId=\"\$articleId\" />",
                ['articleType' => ArticleType::GameModification, 'articleId' => $gameID]
            );

            echo "</div>"; // devboxcontent
            echo "</div>"; // devbox
        }

        if ($isFullyFeaturedGame) {
            echo "<div class='md:float-right mb-4 md:mb-0'>";

            // Only show set request option for logged in users, games without achievements, and core achievement page
            if ($user !== null && $numAchievements == 0 && $flagParam === $officialFlag) {
                ?>
                    <x-game.set-requests :gameId="$gameID" />
                <?php
            }

            echo "</div>";

            if ($flagParam == $unofficialFlag) {
                echo "<h2 class='text-h4'><b>Unofficial</b> Achievements</h2>";
                echo "<a href='/game/$gameID'><b>Click here to view the Core Achievements</b></a><br>";
            } else {
                echo "<h2 class='text-h4'>Achievements</h2>";
            }

            echo "<div class='lg:mb-0'>";
            if ($numAchievements > 0) {
                $numAuthors = count($authorInfo);

                echo "<span class='font-bold'>" . __res('author', $numAuthors) . ":</span> ";
                $i = 0;
                foreach ($authorInfo as $author => $achievementCount) {
                    echo userAvatar($author, icon: false);
                    echo " (" . $achievementCount . ")";
                    if (++$i !== $numAuthors) {
                        echo ', ';
                    }
                }
            }

            // Display claim information
            if ($user !== null && $flagParam === $officialFlag && !$isEventGame) {
                ?>
                    <x-game.claim-info
                        :achievementSetClaims="$gameModel->achievementSetClaims->whereIn('status', [ClaimStatus::Active, ClaimStatus::InReview])"
                        :userPermissions="$permissions"
                    />
                <?php
            }
            echo "</div>";

            echo "<div class='my-8 lg:my-4 lg:flex justify-between w-full gap-x-4'>";
            ?>
                <x-game.achievements-list-meta
                    :isOfficial="$isOfficial"
                    :numAchievements="$numAchievements"
                    :numMissableAchievements="$numMissableAchievements"
                    :totalPossible="$totalPossible"
                    :totalPossibleTrueRatio="$totalPossibleTrueRatio"
                />
            <?php
            echo "</div>";
        }

        // Progression component (mobile only)
        if ($user !== null && $numAchievements > 0) {
            echo "<div class='mt-4 mb-4 lg:hidden'>";
            ?>
            <x-game.current-progress.root
                :beatenGameCreditDialogContext="$beatenGameCreditDialogContext"
                :gameId="$gameID"
                :isBeatable="$isGameBeatable"
                :isBeatenHardcore="$isBeatenHardcore"
                :isBeatenSoftcore="$isBeatenSoftcore"
                :isCompleted="!is_null($userGameProgressionAwards['completed'])"
                :isMastered="!is_null($userGameProgressionAwards['mastered'])"
                :isEvent="$isEventGame"
                :numEarnedHardcoreAchievements="$numEarnedHardcore"
                :numEarnedHardcorePoints="$totalEarnedHardcore"
                :numEarnedSoftcoreAchievements="$numEarnedCasual"
                :numEarnedSoftcorePoints="$totalEarnedCasual"
                :numEarnedWeightedPoints="$totalEarnedTrueRatio"
                :totalAchievementsCount="$numAchievements"
                :totalPointsCount="$totalPossible"
            />
            <?php
            echo "</div>";
        }

        if ($numAchievements > 1) {
            if ($isFullyFeaturedGame) {
                echo "<div class='flex flex-col sm:flex-row-reverse sm:items-end justify-between w-full py-3'>";

                $hasCompletionOrMastery = ($numEarnedCasual === $numAchievements) || ($numEarnedHardcore === $numAchievements);
                $canShowHideUnlockedAchievements = $user && ($numEarnedCasual > 0 || $numEarnedHardcore > 0) && !$hasCompletionOrMastery;
                
                // Check if the user wants this game's unlocked achievements to be hidden by default.
                $hideUnlockedCookie = request()->cookie('hide_unlocked_achievements_games', '');
                $hiddenGameIds = array_filter(array_map('intval', explode(',', $hideUnlockedCookie)));
                $shouldHideUnlocked = in_array($gameID, $hiddenGameIds);
                ?>
                    <x-game.achievements-list-filters
                        :canShowHideUnlockedAchievements="$canShowHideUnlockedAchievements"
                        :numMissableAchievements="$gameMetaBindings['numMissableAchievements']"
                        :gameId="$gameID"
                        :shouldHideUnlocked="$shouldHideUnlocked"
                    />
                <?php
                RenderGameSort($gameModel->ConsoleID, $flagParam?->value, $officialFlag->value, $gameID, $sortBy, canSortByType: $isGameBeatable);
                echo "</div>";
            } elseif ($isEventGame) {
                $now = Carbon::now();
                echo "<div class='justify-between w-full py-3'>";

                RenderGameSort(System::Events, $flagParam?->value, $officialFlag->value, $gameID, $sortBy, canSortByType: $isGameBeatable);
                echo "</div>";
            }
        }

        if ($isFullyFeaturedGame || $isEventGame) {
            if (isset($achievementData)) {
                // Check if unlocked achievements should be hidden (for server-side rendering)
                $hideUnlockedCookie = request()->cookie('hide_unlocked_achievements_games', '');
                $hiddenGameIds = array_filter(array_map('intval', explode(',', $hideUnlockedCookie)));

                // Don't hide unlocked achievements if user has a completion or mastery.
                $hasCompletionOrMastery = ($numEarnedCasual === $numAchievements) || ($numEarnedHardcore === $numAchievements);

                $shouldHideUnlockedAchievements = !$hasCompletionOrMastery && in_array($gameID, $hiddenGameIds);
                ?>
                    <x-game.achievements-list.root
                        :achievements="$achievementData"
                        :beatenGameCreditDialogContext="$beatenGameCreditDialogContext"
                        :isCreditDialogEnabled="$flagParam !== $unofficialFlag"
                        :showAuthorNames="!$isOfficial && isset($user) && $permissions >= Permissions::JuniorDeveloper"
                        :totalPlayerCount="$numDistinctPlayers"
                        :separateUnlockedAchievements="!$isEventGame"
                        :shouldHideUnlocked="$shouldHideUnlockedAchievements"
                    />
                <?php
            }
        }

        echo "<div class='my-5'>";
        ?>
            <x-game.link-buttons
                :allowedLinks="['forum-topic']"
                :game="$gameModel"
            />
        <?php
        echo "</div>";

        if ($isFullyFeaturedGame) {
            $recentPlayerData = getGameRecentPlayers($gameID, 10);
            if (!empty($recentPlayerData)) {
                echo "<div class='mt-6 mb-8'>";
                ?>
                    <x-game.recent-game-players
                        :gameId="$gameID"
                        :gameTitle="$gameTitle"
                        :recentPlayerData="$recentPlayerData"
                    />
                <?php
                echo "</div>";
            }

            echo Blade::render("<x-comment.list :articleType=\"\$articleType\" :articleId=\"\$articleId\" />",
                ['articleType' => ArticleType::Game, 'articleId' => $gameID]
            );
        }
        ?>
    </div>
@if ($isFullyFeaturedGame || $isEventGame)
    <x-slot name="sidebar">
        <?php
        echo "<div class='component text-center mb-6'>";
        echo "<img class='max-w-full rounded-sm' src='" . media_asset($gameData['ImageBoxArt']) . "' alt='Boxart'>";
        echo "</div>";

        if ($isEventGame) {
            echo "<div class='component'>";
            $allowedLinks = ['forum-topic'];
            ?>
                <x-game.link-buttons
                    :game="$gameModel"
                    :isViewingOfficial="$flagParam !== $unofficialFlag"
                    :allowedLinks="$allowedLinks"
                />
            <?php
            echo "</div>";
        } elseif ($isFullyFeaturedGame) {
            echo "<div class='component'>";
            ?>
                <x-game.link-buttons
                    :game="$gameModel"
                    :isViewingOfficial="$flagParam !== $unofficialFlag"
                />
            <?php
            echo "</div>";
        }

        // Progression component (desktop only)
        if ($user !== null && $numAchievements > 0 && $isOfficial) {
            echo "<div class='mb-5 hidden lg:block'>";
            ?>
            <x-game.current-progress.root
                :beatenGameCreditDialogContext="$beatenGameCreditDialogContext"
                :gameId="$gameID"
                :isBeatable="$isGameBeatable"
                :isBeatenHardcore="$isBeatenHardcore"
                :isBeatenSoftcore="$isBeatenSoftcore"
                :isCompleted="!is_null($userGameProgressionAwards['completed'])"
                :isMastered="!is_null($userGameProgressionAwards['mastered'])"
                :isEvent="$isEventGame"
                :numEarnedHardcoreAchievements="$numEarnedHardcore"
                :numEarnedHardcorePoints="$totalEarnedHardcore"
                :numEarnedSoftcoreAchievements="$numEarnedCasual"
                :numEarnedSoftcorePoints="$totalEarnedCasual"
                :numEarnedWeightedPoints="$totalEarnedTrueRatio"
                :totalAchievementsCount="$numAchievements"
                :totalPointsCount="$totalPossible"
            />
            <?php
            echo "</div>";
        }

        $mappedSimilarGames = $allSimilarGames->sortBy('Title')->map($mapGameToAlt);

        $onlySimilarGameSubsets = $mappedSimilarGames
            ->filter(fn (array $game) => str_contains($game['Title'], '[Subset -') && $game['ConsoleName'] !== 'Events')
            ->values()
            ->all();

        $onlySimilarGameEvents = $mappedSimilarGames
            ->filter(fn (array $game) => $game['ConsoleName'] === 'Events')
            ->values()
            ->all();

        $similarGamesWithoutSubsets = $mappedSimilarGames
            ->reject(fn (array $game) => str_contains($game['Title'], '[Subset -') || $game['ConsoleName'] === 'Events')
            ->values()
            ->all();

        if (!empty($onlySimilarGameSubsets)) {
            RenderGameAlts($onlySimilarGameSubsets, 'Subsets');
        }

        if (!empty($onlySimilarGameEvents)) {
            RenderGameAlts(
                $onlySimilarGameEvents,
                'Related Events'
            );
        }

        if (!empty($similarGamesWithoutSubsets)) {
            RenderGameAlts(
                $similarGamesWithoutSubsets,
                'Similar Games'
            );
        }

        if (!empty($gameHubs)) {
            RenderGameAlts($gameHubs, 'Hubs');
        }
        ?>

        @if ($user !== null && $numAchievements > 0)
            <div class="mb-4">
                <x-game.compare-progress
                    :game="$gameModel"
                    :user="$userModel"
                />
            </div>
        @endif

        @if ($numAchievements > 1 && $isOfficial)
            <div id="achdistribution" class="component">
                <h2 class="text-h3">Achievement Distribution</h2>
                <div id="chart_distribution" class="min-h-[260px]"></div>
            </div>

            <x-game.top-achievers :game="$gameModel" />
        @endif

        @if ($gameModel->system->active && !$isEventGame)
            <x-game.leaderboards-listing :game="$gameModel" />
        @endif
    </x-slot>
@endif
</x-app-layout>
