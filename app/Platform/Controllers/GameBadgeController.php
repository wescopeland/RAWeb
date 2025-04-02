<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Http\Controller;
use App\Models\Badge;
use App\Models\Game;
use Illuminate\Contracts\View\View;

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
