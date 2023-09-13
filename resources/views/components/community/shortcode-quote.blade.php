@props([
    'shortcode', // ShortcodeInterface
])

<?php
use App\Community\Models\ForumTopicComment;
use Illuminate\Support\Carbon;
use App\Support\Shortcode\Shortcode;

$content = $shortcode->getContent();
$author = $shortcode->getParameter('author');
$commentId = $shortcode->getParameter('comment');

$postTimestamp = null;
$foundDbComment = null;
$commentUrl = null;
if ($commentId) {
    $foundDbComment = ForumTopicComment::find($commentId);
    if ($foundDbComment) {
        $foundDbComment = $foundDbComment->toArray();

        $postTimestamp = Carbon::parse($foundDbComment['DateCreated'])->format('M j Y, g:ia');
        $commentUrl = url('/viewtopic.php?t=' . $foundDbComment['ForumTopicID'] . '&c=' . $commentId . '#' . $commentId);
    }
}
?>

@if ($content)
    <div class="mt-2">
        @if ($author && $foundDbComment)
            <a class="text-2xs font-bold" href="{{ $commentUrl }}" target="_blank" rel="noreferrer nofollower">
                Quote from {{ $author }} at {{ $postTimestamp }}
            </a>
        @elseif ($foundDbComment)
            <a class="text-2xs font-bold" href="{{ $commentUrl }}" target="_blank" rel="noreferrer nofollower">
                Quote from {{ $foundDbComment['Author'] }} at {{ $postTimestamp }}
            </a>
        @elseif ($author)
            <p class="text-2xs font-bold">
                Quote from: {!! userAvatar($author, icon: false) !!}
            </p>
        @else
            <p class="text-2xs font-bold">
                Quote
            </p>
        @endif

        <div class="bg-embed bg-opacity-50 border-l rounded-r-lg border-text px-4 py-2 max-h-[300px] overflow-y-auto">
            {!! Shortcode::render($content) !!}
        </div>
    </div>
@endif
