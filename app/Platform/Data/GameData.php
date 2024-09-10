<?php

declare(strict_types=1);

namespace App\Platform\Data;

use App\Models\Game;
use App\Platform\Enums\ReleasedAtGranularity;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript('Game')]
class GameData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public Lazy|int $achievementsPublished,
        public Lazy|string $badgeUrl,
        public Lazy|int $forumTopicId,
        public Lazy|Carbon $lastUpdated,
        public Lazy|int $numLeaderboardsVisible,
        public Lazy|int $numUnresolvedTickets,
        public Lazy|int $pointsTotal,
        public Lazy|int $pointsWeighted,
        public Lazy|Carbon $releasedAt,
        public Lazy|ReleasedAtGranularity $releasedAtGranularity,
        public Lazy|SystemData $system,
    ) {
    }

    public static function fromGame(Game $game): self
    {
        return new self(
            id: $game->id,
            title: $game->title,

            achievementsPublished: Lazy::create(fn () => $game->achievements_published),
            badgeUrl: Lazy::create(fn () => $game->badge_url),
            forumTopicId: Lazy::create(fn () => $game->ForumTopicID),
            lastUpdated: Lazy::create(fn () => $game->last_updated),
            numLeaderboardsVisible: Lazy::create(fn () => $game->leaderboards->count()),
            numUnresolvedTickets: Lazy::create(fn () => $game->tickets->count()),
            pointsTotal: Lazy::create(fn () => $game->points_total),
            pointsWeighted: Lazy::create(fn () => $game->points_weighted),
            releasedAt: Lazy::create(fn () => $game->released_at),
            releasedAtGranularity: Lazy::create(fn () => $game->released_at_granularity),
            system: Lazy::create(fn () => SystemData::fromSystem($game->system)),
        );
    }
}
