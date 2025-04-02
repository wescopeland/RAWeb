<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Http\Controller;
use App\Models\Achievement;
use App\Platform\Requests\AchievementRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AchievementController extends Controller
{
    protected function resourceName(): string
    {
        return 'achievement';
    }

    public function index(): View
    {
        Gate::authorize('viewAny', $this->resourceClass());

        return view('resource.index')
            ->with('resource', $this->resourceName());
    }

    public function show(Achievement $achievement, ?string $slug = null): View|RedirectResponse
    {
        Gate::authorize('view', $achievement);

        if (!$this->resolvesToSlug($achievement->slug, $slug)) {
            return redirect($achievement->canonicalUrl);
        }

        $achievement->loadMissing([
            'game',
            'user',
        ]);

        return view($this->resourceName() . '.show')->with('achievement', $achievement);
    }

    public function edit(Achievement $achievement): View
    {
        Gate::authorize('update', $achievement);

        $achievement->load([
            'game' => function ($query) {
                // $query->with('memoryNotes');
            },
            'user',
        ]);

        return view($this->resourceName() . '.edit')->with('achievement', $achievement);
    }

    public function update(AchievementRequest $request, Achievement $achievement): RedirectResponse
    {
        Gate::authorize('update', $achievement);

        $achievement->fill($request->validated())->save();

        return back()->with('success', $this->resourceActionSuccessMessage('achievement', 'update'));
    }
}
