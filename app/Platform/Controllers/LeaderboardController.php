<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use App\Http\Controller;
use App\Models\Leaderboard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LeaderboardController extends Controller
{
    protected function resourceName(): string
    {
        return 'leaderboard';
    }

    public function index(): View
    {
        Gate::authorize('viewAny', $this->resourceClass());

        return view('resource.index')
            ->with('resource', $this->resourceName());
    }

    public function create(): void
    {
    }

    public function store(Request $request): void
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Leaderboard $leaderboard, ?string $slug = null): View|RedirectResponse
    {
        Gate::authorize('view', $leaderboard);

        if (!$this->resolvesToSlug($leaderboard->slug, $slug)) {
            return redirect($leaderboard->canonicalUrl);
        }

        return view('leaderboard.show')->with('leaderboard', $leaderboard);
    }

    public function edit(Leaderboard $leaderboard): void
    {
    }

    public function update(Request $request, Leaderboard $leaderboard): void
    {
    }

    public function destroy(Leaderboard $leaderboard): void
    {
    }
}
