<?php

declare(strict_types=1);

namespace App\Community\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Community\Actions\GetUrlToCommentDestinationAction;
use App\Http\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function show(
        Comment $comment,
        GetUrlToCommentDestinationAction $getUrlToCommentDestinationAction,
    ): RedirectResponse {
        Gate::authorize('view', $comment);

        abort_if($comment->commentable === null, 404);

        return redirect($getUrlToCommentDestinationAction->execute($comment));
    }
}
