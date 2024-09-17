<?php

declare(strict_types=1);

namespace App\Community\Controllers;

use App\Community\Data\UserGameListPagePropsData;
use App\Community\Enums\UserGameListType;
use App\Data\UserPermissionsData;
use App\Http\Controller;
use App\Models\User;
use App\Models\UserGameListEntry;
use App\Platform\Actions\BuildGameListAction;
use App\Platform\Data\SystemData;
use App\Platform\Enums\GameListType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class UserGameListController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        // TODO tests
        // TODO test light mode
        // TODO filter by tags (use same types as beaten game leaderboard)
        // TODO long loading
        // TODO column pinning on mobile
        // TODO genericize the table
        // TODO don't duplicate state in the WantToPlayGamesRoot SSR hydration
        // TODO show user progress
        // TODO filter by user progress
        // TODO gate tickets column behind develop ability

        // NEXT STEPS:
        // TODO allow for url params
        // TODO remember the user's state settings somehow, Laravel session + redis ?

        /** @var User $user */
        $user = $request->user();

        $paginatedData = (new BuildGameListAction())->execute(
            GameListType::UserPlay,
            user: $user,
            page: 1,
        );

        // Only allow filtering by systems the user has games on their list for.
        $filterableSystemOptions = $user->gameListEntries(UserGameListType::Play)
            ->with('game.system')
            ->get()
            ->pluck('game.system')
            ->unique('id')
            ->map(fn ($system) => SystemData::fromSystem($system))
            ->values()
            ->all();

        $can = UserPermissionsData::fromUser($user)->include('develop');

        $props = new UserGameListPagePropsData(
            paginatedGameListEntries: $paginatedData,
            filterableSystemOptions: $filterableSystemOptions,
            can: $can,
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
