<?php

use App\Community\Enums\ArticleType;
use App\Enums\ClientSupportLevel;
use App\Enums\Permissions;
use App\Models\GameAchievementSet;
use App\Models\GameHash;
use App\Models\Leaderboard;
use App\Models\LeaderboardEntry;
use App\Models\User;
use App\Platform\Actions\ResumePlayerSessionAction;
use App\Platform\Actions\UpsertTriggerVersionAction;
use App\Platform\Enums\ValueFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

// TODO migrate to action
function SubmitLeaderboardEntry(
    User $user,
    Leaderboard $leaderboard,
    int $newEntry,
    ?string $validation,
    ?GameHash $gameHash = null,
    ?Carbon $timestamp = null,
    ClientSupportLevel $clientSupportLevel = ClientSupportLevel::Full,
): array {
    $retVal = ['Success' => true];

    $leaderboard->loadMissing('game');
    if ($leaderboard->game->ConsoleID && !isValidConsoleId($leaderboard->game->ConsoleID)) {
        $retVal['Success'] = false;
        $retVal['Error'] = "Cannot submit entry for unsupported console";

        return $retVal;
    }

    $retVal['LBData'] = [
        'Format' => $leaderboard->Format,
        'LeaderboardID' => $leaderboard->id,
        'GameID' => $leaderboard->GameID,
        'Title' => $leaderboard->Title,
        'LowerIsBetter' => $leaderboard->LowerIsBetter,
    ];
    $retVal['Score'] = $newEntry;
    $retVal['ScoreFormatted'] = ValueFormat::format($newEntry, $leaderboard->Format);

    $timestamp ??= Carbon::now();
    $playerSession = app()->make(ResumePlayerSessionAction::class)->execute(
        $user,
        $leaderboard->game,
        ($gameHash && !$gameHash->isMultiDiscGameHash()) ? $gameHash : null,
        timestamp: $timestamp,
    );

    // First check if there's an existing entry (including soft-deleted)
    $existingLeaderboardEntry = LeaderboardEntry::withTrashed()
        ->where('leaderboard_id', $leaderboard->id)
        ->where('user_id', $user->id)
        ->first();

    if ($existingLeaderboardEntry) {
        if ($clientSupportLevel !== ClientSupportLevel::Full) {
            $retVal['BestScore'] = $existingLeaderboardEntry->score;
        } elseif ($existingLeaderboardEntry->trashed()
            || $leaderboard->isBetterScore($newEntry, $existingLeaderboardEntry->score)) {

            // Update the score first before saving/restoring to avoid race conditions with observers.
            $existingLeaderboardEntry->score = $newEntry;
            $existingLeaderboardEntry->player_session_id = $playerSession->id;
            $existingLeaderboardEntry->updated_at = $timestamp;

            if ($existingLeaderboardEntry->trashed()) {
                $existingLeaderboardEntry->restore();
            } else {
                $existingLeaderboardEntry->save();
            }

            $retVal['BestScore'] = $newEntry;
        } else {
            // No change made.
            $retVal['BestScore'] = $existingLeaderboardEntry->score;
        }
    } elseif ($clientSupportLevel !== ClientSupportLevel::Full) {
        $retVal['BestScore'] = 0;
    } else {
        // No existing leaderboard entry. Let's insert a new one, using
        // updateOrCreate to handle potential race conditions if the client
        // is rapid-firing off submissions to the server.
        $entry = LeaderboardEntry::updateOrCreate(
            [
                'leaderboard_id' => $leaderboard->id,
                'user_id' => $user->id,
            ],
            [
                'score' => $newEntry,
                'player_session_id' => $playerSession->id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null, // Ensure the entry is not soft-deleted.
            ]
        );

        $retVal['BestScore'] = $newEntry;
    }

    $retVal['RankInfo'] = [
        'NumEntries' => $leaderboard->entries()->count(),
    ];

    $retVal['RankInfo']['Rank'] = $leaderboard->getRank($retVal['BestScore']);
    $entries = $leaderboard->sortedEntries();

    $getEntries = function (Builder $query) {
        $entries = $query
            ->with('user')
            ->limit(10)
            ->get()
            ->map(fn ($entry) => [
                'User' => $entry->user->User,
                'Score' => $entry->score,
                'DateSubmitted' => $entry->updated_at->unix(),
            ])
            ->toArray();

        $index = 1;
        $rank = 0;
        $score = null;
        foreach ($entries as &$entry) {
            if ($entry['Score'] !== $score) {
                $score = $entry['Score'];
                $rank = $index;
            }

            $entry['Rank'] = $rank;
            $index++;
        }

        return $entries;
    };

    $retVal['TopEntries'] = $getEntries($entries->getQuery());

    $retVal['TopEntriesFriends'] = $getEntries($entries->whereHas('user', function ($query) use ($user) {
        $friends = $user->followedUsers()->pluck('related_user_id');
        $friends[] = $user->id;
        $query->whereIn('ID', $friends);
    })->getQuery());

    return $retVal;
}

function submitLBData(
    string $user,
    int $lbID,
    string $lbMem,
    string $lbTitle,
    string $lbDescription,
    string $lbFormat,
    bool $lbLowerIsBetter,
    int $lbDisplayOrder
): bool {
    sanitize_sql_inputs($user, $lbMem, $lbTitle, $lbDescription, $lbFormat);

    $lbLowerIsBetter = (int) $lbLowerIsBetter;

    $query = "UPDATE LeaderboardDef AS ld SET
              ld.Mem = '$lbMem',
              ld.Format = '$lbFormat',
              ld.Title = '$lbTitle',
              ld.Description = '$lbDescription',
              ld.Format = '$lbFormat',
              ld.LowerIsBetter = '$lbLowerIsBetter',
              ld.DisplayOrder = '$lbDisplayOrder'
              WHERE ld.ID = $lbID";

    $db = getMysqliConnection();
    $dbResult = mysqli_query($db, $query);
    if ($dbResult !== false) {
        return true;
    }

    return false;
}

function SubmitNewLeaderboard(int $gameID, ?int &$lbIDOut, User $user): bool
{
    if ($gameID == 0) {
        return false;
    }

    $defaultMem = "STA:0x0000=h0010_0xhf601=h0c::CAN:0xhfe13<d0xhfe13::SUB:0xf7cc!=0_d0xf7cc=0::VAL:0xhfe24*1_0xhfe25*60_0xhfe22*3600";
    $query = "INSERT INTO LeaderboardDef (GameID, Mem, Format, Title, Description, LowerIsBetter, DisplayOrder, author_id, Created)
                                VALUES ($gameID, '$defaultMem', 'SCORE', 'My Leaderboard', 'My Leaderboard Description', 0,
                                (SELECT * FROM (SELECT COALESCE(Max(DisplayOrder) + 1, 0) FROM LeaderboardDef WHERE  GameID = $gameID) AS temp), {$user->id}, NOW())";

    $dbResult = s_mysql_query($query);
    if ($dbResult !== false) {
        $db = getMysqliConnection();
        $lbIDOut = mysqli_insert_id($db);

        return true;
    }

    return false;
}

function UploadNewLeaderboard(
    string $authorUsername,
    ?int $gameID,
    string $title,
    string $desc,
    string $format,
    bool $lowerIsBetter,
    string $mem,
    ?int &$idInOut,
    ?string &$errorOut,
    ?int $gameAchievementSetID
): bool {
    if (!$gameAchievementSetID && !$gameID) {
        $errorOut = "You must provide a game ID or a game achievement set ID.";

        return false;
    }

    if ($gameAchievementSetID) {
        $gameAchievementSet = GameAchievementSet::find($gameAchievementSetID);
        if (!$gameAchievementSet) {
            $errorOut = "Game achievement set not found.";

            return false;
        }

        $gameID = $gameAchievementSet->game_id;
    }

    $displayOrder = 0;
    $originalAuthor = null;

    /** @var ?Leaderboard $foundLeaderboard */
    $foundLeaderboard = null;

    if ($idInOut > 0) {
        $foundLeaderboard = Leaderboard::find($idInOut);
        if ($foundLeaderboard) {
            $displayOrder = $foundLeaderboard->order_column;
            $originalAuthor = $foundLeaderboard->developer;
        } else {
            $errorOut = "Unknown leaderboard";

            return false;
        }
    }

    $authorModel = User::whereName($authorUsername)->first();

    // Prevent non-developers from uploading or modifying leaderboards
    $userPermissions = (int) $authorModel->getAttribute('Permissions');
    if ($userPermissions < Permissions::Developer) {
        if (
            $userPermissions < Permissions::JuniorDeveloper
            || (isset($originalAuthor) && !$authorModel->is($originalAuthor))
        ) {
            $errorOut = "You must be a developer to perform this action! Please drop a message in the forums to apply.";

            return false;
        }
    }

    if (!isValidConsoleId(getGameData($gameID)['ConsoleID']) && !hasSetClaimed($authorModel, $gameID, false)) {
        $errorOut = "You cannot promote leaderboards for a game from an unsupported console (console ID: " . getGameData($gameID)['ConsoleID'] . ") unless you have an active claim on the game.";

        return false;
    }

    if (!ValueFormat::isValid($format)) {
        $errorOut = "Unknown format: $format";

        return false;
    }

    if (!isset($idInOut) || $idInOut == 0) {
        if (!SubmitNewLeaderboard($gameID, $idInOut, $authorModel)) {
            $errorOut = "Internal error creating new leaderboard.";

            return false;
        }

        $foundLeaderboard = Leaderboard::find($idInOut);
        if ($foundLeaderboard) {
            $displayOrder = $foundLeaderboard->order_column;
        }
    }

    if (!submitLBData($authorUsername, $idInOut, $mem, $title, $desc, $format, $lowerIsBetter, $displayOrder)) {
        $errorOut = "Internal error updating leaderboard.";

        return false;
    }

    if (isset($originalAuthor)) {
        addArticleComment("Server", ArticleType::Leaderboard, $idInOut,
            "{$authorModel->display_name} edited this leaderboard.", $authorModel->username
        );
    }

    (new UpsertTriggerVersionAction())->execute(
        $foundLeaderboard,
        $mem,
        versioned: true, // we don't currently support unpublished leaderboards
        user: $authorModel,
    );

    return true;
}
