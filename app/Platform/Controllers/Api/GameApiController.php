<?php

namespace App\Platform\Controllers\Api;

use App\Actions\GetUserDeviceKindAction;
use App\Http\Controller;
use App\Models\Game;
use App\Models\User;
use App\Platform\Actions\BuildGameListAction;
use App\Platform\Actions\GetRandomGameAction;
use App\Platform\Enums\GameListType;
use App\Platform\Requests\GameListRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameApiController extends Controller
{
    public function index(GameListRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Game::class);

        $isMobile = (new GetUserDeviceKindAction())->execute() === 'mobile';

        $paginatedData = (new BuildGameListAction())->execute(
            GameListType::AllGames,
            user: $request->user(),
            page: $request->getPage(),
            filters: $request->getFilters(),
            sort: $request->getSort(),
            perPage: $isMobile ? 100 : $request->getPageSize(),
        );

        return response()->json($paginatedData);
    }

    public function store(): void
    {
    }

    public function show(): void
    {
    }

    public function update(): void
    {
    }

    public function destroy(): void
    {
    }

    public function random(GameListRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Game::class);

        $randomGame = (new GetRandomGameAction())->execute(
            GameListType::AllGames,
            user: $request->user(),
            filters: $request->getFilters(),
        );

        return response()->json(['gameId' => $randomGame->id]);
    }

    public function generateOfficialForumTopic(Request $request, Game $game): JsonResponse
    {
        $this->authorize('createForumTopic', $game);

        /** @var User $user */
        $user = $request->user();

        $forumTopicComment = generateGameForumTopic($user, $game->id);
        if (!$forumTopicComment) {
            return response()->json(['success' => false], 500);
        }

        return response()->json(['success' => true, 'topicId' => $forumTopicComment->forumTopic->id]);
    }
}
