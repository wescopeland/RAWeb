<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use App\Http\Controller;
use App\Models\Badge;
use App\Models\Game;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class GameBadgeController extends Controller
{
    public function index(Game $game): View
    {
        Gate::authorize('view', $game);
        Gate::authorize('viewAny', Badge::class);

        return view('server.game.badge.index')
            ->with('game', $game);
    }
}
