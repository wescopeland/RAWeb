<?php

namespace App\Community\Controllers\Api;

use App\Http\Controller;
use App\Models\UserGameListEntry;
use App\Platform\Actions\BuildGameListAction;
use App\Platform\Enums\GameListType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserGameListApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sortParam = $request->input('sort', 'title'); // Default to "title".
        $sortDirection = 'asc';

        if (str_starts_with($sortParam, '-')) {
            $sortDirection = 'desc';
            $sortParam = ltrim($sortParam, '-'); // "-title" -> "title"
        }

        $paginatedData = (new BuildGameListAction())->execute(
            GameListType::UserPlay,
            user: $request->user(),
            page: $request->input('page', 1),
            filters: $this->extractFiltersFromRequest($request),
            sort: [
                'field' => $sortParam,
                'direction' => $sortDirection,
            ],
        );

        return response()->json($paginatedData);
    }

    public function destroy(Request $request, int $gameId): JsonResponse
    {
        $user = $request->user();
        $userGameListEntry = UserGameListEntry::where('user_id', $user->id)
            ->where('GameID', $gameId)
            ->first();
    
        if ($userGameListEntry) {
            $userGameListEntry->delete();
        }

        return response()->json(['success' => true]);
    }

    protected function extractFiltersFromRequest(Request $request): array
    {
        $filters = [];

        foreach ($request->query('filter', []) as $key => $value) {
            // Convert comma-separated values into an array.
            $filters[$key] = explode(',', $value);
        }

        return $filters;
    }
}
