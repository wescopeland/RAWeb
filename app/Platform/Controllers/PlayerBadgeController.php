<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use App\Http\Controller;
use App\Models\PlayerBadge;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlayerBadgeController extends Controller
{
    public function index(User $player): View
    {
        Gate::authorize('viewAny', [PlayerBadge::class, $player]);

        return view('player.badge.index')
            ->with('user', $player);
    }

    public function create(): void
    {
        Gate::authorize('create', PlayerBadge::class);
    }

    public function store(Request $request): void
    {
        Gate::authorize('create', PlayerBadge::class);
    }

    public function show(PlayerBadge $playerBadge): void
    {
        Gate::authorize('view', $playerBadge);
    }

    public function edit(PlayerBadge $playerBadge): void
    {
        Gate::authorize('update', $playerBadge);
    }

    public function update(Request $request, PlayerBadge $playerBadge): void
    {
        Gate::authorize('update', $playerBadge);
    }

    public function destroy(PlayerBadge $playerBadge): void
    {
        Gate::authorize('delete', $playerBadge);
    }
}
