<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Http\Controller;
use App\Models\Achievement;
use Illuminate\Contracts\View\View;

class AchievementPlayerController extends Controller
{
    protected function resourceName(): string
    {
        return 'achievement.player';
    }

    public function index(Achievement $achievement): View
    {
        Gate::authorize('view', $achievement);

        $achievement->loadMissing([
            'game',
        ]);

        return view('achievement.player.index')
            ->with('achievement', $achievement);
    }
}
