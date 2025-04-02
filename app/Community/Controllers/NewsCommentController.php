<?php

declare(strict_types=1);

namespace App\Community\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Community\Actions\AddCommentAction;
use App\Community\Actions\GetUrlToCommentDestinationAction;
use App\Community\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\News;
use App\Models\NewsComment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsCommentController extends CommentController
{
    public function index(Request $request, News $news): RedirectResponse
    {
        return redirect($news->canonicalUrl . '?' . $request->getQueryString());
    }

    /**
     * @see UserCommentController::create()
     */
    public function create(): void
    {
    }

    public function store(
        StoreCommentRequest $request,
        News $news,
        AddCommentAction $addCommentAction,
        GetUrlToCommentDestinationAction $getUrlToCommentDestinationAction,
    ): RedirectResponse {
        Gate::authorize('create', [NewsComment::class, $news]);

        /** @var false|Comment $comment */
        $comment = $addCommentAction->execute($request, $news);

        if (!$comment) {
            return back()->with('error', $this->resourceActionErrorMessage('news.comment', 'create'));
        }

        return redirect($getUrlToCommentDestinationAction->execute($comment))
            ->with('success', $this->resourceActionSuccessMessage('news.comment', 'create'));
    }

    public function edit(NewsComment $comment): View
    {
        Gate::authorize('update', $comment);

        return view('news.comment.edit')
            ->with('comment', $comment);
    }

    protected function update(
        StoreCommentRequest $request,
        NewsComment $comment,
        GetUrlToCommentDestinationAction $getUrlToCommentDestinationAction,
    ): RedirectResponse {
        Gate::authorize('update', $comment);

        $comment->fill($request->validated())->save();

        return redirect($getUrlToCommentDestinationAction->execute($comment))
            ->with('success', $this->resourceActionSuccessMessage('news.comment', 'update'));
    }

    protected function destroy(NewsComment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $return = $comment->commentable->canonicalUrl;

        /*
         * don't touch
         */
        $comment->timestamps = false;
        $comment->delete();

        return redirect($return)
            ->with('success', $this->resourceActionSuccessMessage('news.comment', 'delete'));
    }

    public function destroyAll(News $news): RedirectResponse
    {
        Gate::authorize('deleteComments', $news);

        $news->comments()->delete();

        return back()->with('success', $this->resourceActionSuccessMessage('news.comment', 'delete'));
    }
}
