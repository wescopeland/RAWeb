<?php

declare(strict_types=1);

namespace App\Community\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Community\Requests\ForumRequest;
use App\Models\Forum;
use App\Models\ForumCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ForumController extends \App\Http\Controller
{
    public function index(): void
    {
        Gate::authorize('viewAny', Forum::class);
    }

    public function create(ForumCategory $forumCategory): void
    {
        Gate::authorize('store', [Forum::class, $forumCategory]);
    }

    public function store(Request $request, ForumCategory $forumCategory): void
    {
        Gate::authorize('store', [Forum::class, $forumCategory]);
    }

    public function show(Forum $forum, ?string $slug = null): View|RedirectResponse
    {
        Gate::authorize('view', $forum);

        if (!$this->resolvesToSlug($forum->slug, $slug)) {
            return redirect($forum->canonicalUrl);
        }

        $forum->withCount('topics');

        $topics = $forum->topics()
            ->withCount('comments')
            ->with('latestComment')
            ->orderbyLatestActivity('desc')
            ->paginate();

        return view('forum.show')
            ->with('category', $forum->category)
            ->with('topics', $topics)
            ->with('forum', $forum);
    }

    public function edit(Forum $forum): View
    {
        Gate::authorize('update', $forum);

        return view('forum.edit')->with('forum', $forum);
    }

    public function update(ForumRequest $request, Forum $forum): RedirectResponse
    {
        Gate::authorize('update', $forum);

        $forum->fill($request->validated())->save();

        return back()->with('success', $this->resourceActionSuccessMessage('forum', 'update'));
    }

    public function destroy(Forum $forum): void
    {
        Gate::authorize('delete', $forum);

        dd('implement');
    }
}
