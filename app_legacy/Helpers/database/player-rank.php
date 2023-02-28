<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use LegacyApp\Community\Enums\Rank;
use LegacyApp\Community\Enums\RankType;
use LegacyApp\Platform\Enums\AchievementType;
use LegacyApp\Platform\Enums\UnlockMode;
use LegacyApp\Site\Models\User;

function SetUserUntrackedStatus($usernameIn, $isUntracked): void
{
    sanitize_sql_inputs($usernameIn, $isUntracked);

    $query = "UPDATE UserAccounts SET Untracked = $isUntracked, Updated=NOW() WHERE User = '$usernameIn'";
    s_mysql_query($query);
}

function getPlayerPoints(string $user, &$dataOut): bool
{
    if (mb_strlen($user) < 2) {
        return false;
    }

    $query = "SELECT ua.RAPoints, ua.RASoftcorePoints
              FROM UserAccounts AS ua
              WHERE ua.User=:username";

    $dataOut = legacyDbFetch($query, ['username' => $user]);
    if ($dataOut) {
        settype($dataOut['RAPoints'], 'integer');
        settype($dataOut['RASoftcorePoints'], 'integer');

        return true;
    }

    return false;
}

function recalculatePlayerPoints($user): bool
{
    sanitize_sql_inputs($user);

    $query = "UPDATE UserAccounts ua
                LEFT JOIN (
                    SELECT aw.User AS UserAwarded,
                    SUM(IF(aw.HardcoreMode = " . UnlockMode::Hardcore . ", ach.Points, 0)) AS HardcorePoints,
                    SUM(IF(aw.HardcoreMode = " . UnlockMode::Hardcore . ", ach.TrueRatio, 0)) AS TruePoints,
                    SUM(IF(aw.HardcoreMode = " . UnlockMode::Softcore . ", ach.Points, 0)) AS TotalPoints
                    FROM Awarded AS aw
                    LEFT JOIN Achievements AS ach ON ach.ID = aw.AchievementID
                    WHERE aw.User = '$user' AND ach.Flags = " . AchievementType::OfficialCore . "
                ) hc ON ua.User = hc.UserAwarded
                SET RAPoints = COALESCE(hc.HardcorePoints, 0),
                    TrueRAPoints = COALESCE(hc.TruePoints, 0),
                    RASoftcorePoints = COALESCE(hc.TotalPoints - hc.HardcorePoints, 0)
                WHERE User = '$user'";

    $dbResult = s_mysql_query($query);
    if (!$dbResult) {
        return false;
    }

    return true;
}

function countRankedUsers(int $type = RankType::Hardcore): int
{
    $query = "SELECT COUNT(*) AS count FROM UserAccounts ";
    switch ($type) {
        case RankType::Hardcore:
            $query .= "WHERE RAPoints >= " . Rank::MIN_POINTS;
            break;

        case RankType::Softcore:
            $query .= "WHERE RASoftcorePoints >= " . Rank::MIN_POINTS;
            break;

        case RankType::TruePoints:
            $query .= "WHERE TrueRAPoints >= " . Rank::MIN_TRUE_POINTS;
            break;
    }

    $query .= " AND NOT Untracked";

    return (int) legacyDbFetch($query)['count'];
}

function getTopUsersByScore($count, &$dataOut, $ofFriend = null): int
{
    sanitize_sql_inputs($count, $ofFriend);
    settype($count, 'integer');

    if ($count > 10) {
        $count = 10;
    }

    $subquery = "WHERE !ua.Untracked";
    if (isset($ofFriend)) {
        $friendSubquery = GetFriendsSubquery($ofFriend);
        $subquery = "WHERE !ua.Untracked AND ua.User IN ($friendSubquery)";
    }

    $query = "SELECT User, RAPoints, TrueRAPoints
              FROM UserAccounts AS ua
              $subquery
              ORDER BY RAPoints DESC
              LIMIT 0, $count ";

    $dbResult = s_mysql_query($query);

    if (!$dbResult || mysqli_num_rows($dbResult) == 0) {
        // This is acceptable if the user doesn't have any friends!
        return 0;
    } else {
        $i = 0;
        while ($db_entry = mysqli_fetch_assoc($dbResult)) {
            // $dataOut[$i][0] = $db_entry["ID"];
            $dataOut[$i][1] = $db_entry["User"];
            $dataOut[$i][2] = $db_entry["RAPoints"];
            $dataOut[$i][3] = $db_entry["TrueRAPoints"];
            $i++;
        }

        return $i;
    }
}

/**
 * Gets the points or retro points rank of the user.
 */
function getUserRank(string $username, int $type = RankType::Hardcore): ?int
{
    $key = "user:$username:rank:";
    $key .= match ($type) {
        default => 'hardcore',
        RankType::Softcore => 'softcore',
        RankType::TruePoints => 'truepoints',
    };

    return Cache::remember($key, Carbon::now()->addMinutes(15), function () use ($username, $type) {
        $user = User::firstWhere('User', $username);
        if (!$user || $user->Untracked) {
            return null;
        }

        $points = 0;
        $field = '';
        switch ($type) {
            default: // hardcore
                $points = $user->RAPoints;
                if ($points < Rank::MIN_POINTS) {
                    return null;
                }

                $field = 'RAPoints';
                break;

            case RankType::Softcore:
                $points = $user->RASoftcorePoints;
                if ($points < Rank::MIN_POINTS) {
                    return null;
                }

                $field = 'RASoftcorePoints';
                break;

            case RankType::TruePoints:
                $points = $user->TrueRAPoints;
                if ($points < Rank::MIN_TRUE_POINTS) {
                    return null;
                }

                $field = 'TrueRAPoints';
                break;
        }

        $query = "SELECT ( COUNT(*) + 1 ) AS UserRank
                  FROM UserAccounts AS ua
                  WHERE ua.$field > $points AND NOT ua.Untracked";

        return (int) legacyDbFetch($query)['UserRank'];
    });
}
