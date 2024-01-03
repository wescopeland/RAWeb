<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use App\Community\Enums\TicketState;
use App\Community\Models\Ticket;
use App\Http\Controller;
use App\Platform\Models\Game;
use App\Platform\Models\GameAlternative;
use App\Platform\Models\PlayerGame;
use App\Platform\Models\System;
use App\Site\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatedGamesTableController extends GameListControllerBase
{
    public function __invoke(Request $request): View
    {
        $gameId = $request->route('game');
        $game = Game::firstWhere('ID', $gameId);
        if ($game === null) {
            abort(404);
        }

        $validatedData = $request->validate([
            'sort' => 'sometimes|string|in:console,title,achievements,points,leaderboards,players,tickets,progress,retroratio,-title,-achievements,-points,-leaderboards,-players,-tickets,-progress,-retroratio',
            'filter.console' => 'sometimes|in:true,false',
            'filter.populated' => 'sometimes|in:true,false',
        ]);
        $sortOrder = $validatedData['sort'] ?? 'title';
        $filterOptions = [
            'console' => ($validatedData['filter']['console'] ?? 'false') !== 'false',
            'populated' => ($validatedData['filter']['populated'] ?? 'false') !== 'false',
        ];

        $gameIDs = GameAlternative::where('gameID', $gameId)->pluck('gameIDAlt')->toArray()
                 + GameAlternative::where('gameIDAlt', $gameId)->pluck('gameID')->toArray();

        $userProgress = $this->getUserProgress($gameIDs);
        [$games, $consoles] = $this->getGameList($gameIDs, $userProgress);

        // ignore hubs
        $games = array_filter($games, function ($game) {
            return $game['ConsoleID'] != 100;
        });
        $consoles = $consoles->filter(function ($console) {
            return $console['ID'] != 100;
        });

        if ($filterOptions['populated']) {
            $games = array_filter($games, function ($game) {
                return $game['achievements_published'] > 0;
            });
        }

        $user = $request->user();
        if ($user !== null) {
            $this->mergeWantToPlay($games, $user);
        }

        $this->sortGameList($games, $sortOrder);

        return view('platform.components.game.related-games-table', [
            'consoles' => $consoles,
            'games' => $games,
            'sortOrder' => $sortOrder,
            'filterOptions' => $filterOptions,
            'userProgress' => $userProgress,
        ]);
    }
}
