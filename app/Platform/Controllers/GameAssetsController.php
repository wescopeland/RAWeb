<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use App\Http\Controller;
use App\Models\Game;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Gate;

class GameAssetsController extends Controller
{
    public function index(Game $game): View
    {
        Gate::authorize('view', $game);

        $game->loadMissing(['gameHashSets' => function (HasMany $query) {
            $query->orderByDesc('compatible');
            $query->with('hashes');
        }]);

        return view('server.game.assets')
            ->with('game', $game);
    }
}
