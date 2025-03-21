<?php

use App\Enums\SearchType;

if (!request()->has('term')) {
    return response()->json([]);
}

$searchTerm = request()->post('term');
if (strlen($searchTerm) < 2) {
    return response()->json([]);
}
sanitize_sql_inputs($searchTerm);

$source = request()->post('source');

$maxResults = 10;
$permissions = 0; /* permissions only needed for searching forums */

$results = [];
if ($source == 'game') {
    $order = [SearchType::Game];
} elseif ($source == 'achievement') {
    $order = [SearchType::Achievement];
} elseif ($source == 'user' || $source == 'game-compare') {
    $order = [SearchType::User];
} else {
    $order = [SearchType::Game, SearchType::Hub, SearchType::Achievement, SearchType::User];
}

performSearch($order, $searchTerm, 0, $maxResults, $permissions, $results, wantTotalResults: false);

$dataOut = [];
foreach ($results as $nextRow) {
    $result = [
        'label' => $nextRow['Title'] ?? null,
        'mylink' => $nextRow['Target'] ?? null,
    ];

    if ($source === 'user' || $source === 'game-compare') {
        $result['username'] = $nextRow['ID'] ?? null;
    }

    $dataOut[] = $result;
}

return response()->json($dataOut);
