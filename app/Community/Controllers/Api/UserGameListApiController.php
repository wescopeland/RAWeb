<?php

namespace App\Community\Controllers\Api;

use App\Community\Requests\UserGameListRequest;
use App\Http\Controller;
use App\Models\UserGameListEntry;
use App\Platform\Actions\BuildGameListAction;
use App\Platform\Enums\GameListType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserGameListApiController extends Controller
{
    public function index(UserGameListRequest $request): JsonResponse
    {
        $paginatedData = (new BuildGameListAction())->execute(
            GameListType::UserPlay,
            user: $request->user(),
            page: $request->getPage(),
            filters: $request->getFilters(),
            sort: $request->getSort(),
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
}
