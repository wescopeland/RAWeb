<?php

declare(strict_types=1);

namespace App\Community\Controllers;

use App\Community\Data\UserGameListPagePropsData;
use App\Community\Enums\UserGameListType;
use App\Http\Controller;
use App\Models\UserGameListEntry;
use Illuminate\Http\Request;
use App\Models\User;
use App\Platform\Actions\BuildGameListAction;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Platform\Data\SystemData;
use App\Platform\Enums\GameListType;

class UserGameListController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        // TODO request object validation
        // TODO tests
        // TODO remember the user's state settings somehow, Laravel session + redis ?
        // TODO reset table to default button
        // TODO test light mode
        // TODO filter by tags
        // TODO long loading
        // TODO column pinning on mobile
        // TODO genericize the table
        // TODO don't duplicate state in the WantToPlayGamesRoot SSR hydration

        /** @var User $user */
        $user = $request->user();

        $paginatedData = (new BuildGameListAction())->execute(
            GameListType::UserPlay,
            user: $user,
            page: $request->input('page', 1),
        );

        // Only allow filtering by systems the user has games on their list for.
        $filterableSystemOptions = $user->gameListEntries(UserGameListType::Play)
            ->with('game.system')
            ->get()
            ->pluck('game.system')
            ->unique('id')
            ->map(fn($system) => SystemData::fromSystem($system))
            ->values()
            ->all();
                        
        $props = new UserGameListPagePropsData(
            paginatedGameListEntries: $paginatedData,
            filterableSystemOptions: $filterableSystemOptions,
        );

        return Inertia::render('game-list/play', $props);
    }

    public function create(): void
    {
    }

    public function store(Request $request): void
    {
    }

    public function show(UserGameListEntry $userGameListEntry): void
    {
    }

    public function edit(UserGameListEntry $userGameListEntry): void
    {
    }

    public function update(Request $request, UserGameListEntry $userGameListEntry): void
    {
    }

    public function destroy(UserGameListEntry $userGameListEntry): void
    {
    }
}
