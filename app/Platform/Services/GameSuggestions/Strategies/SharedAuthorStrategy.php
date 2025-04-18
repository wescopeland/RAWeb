<?php

declare(strict_types=1);

namespace App\Platform\Services\GameSuggestions\Strategies;

use App\Data\UserData;
use App\Models\Achievement;
use App\Models\Game;
use App\Models\System;
use App\Models\User;
use App\Platform\Data\GameData;
use App\Platform\Data\GameSuggestionContextData;
use App\Platform\Enums\AchievementFlag;
use App\Platform\Enums\GameSuggestionReason;
use App\Platform\Services\GameSuggestions\Enums\SourceGameKind;

class SharedAuthorStrategy implements GameSuggestionStrategy
{
    private ?Game $selectedGame = null;
    private ?User $selectedAuthor = null;

    public function __construct(
        private readonly Game $sourceGame,
        private readonly ?SourceGameKind $sourceGameKind = null,
        private readonly bool $attachSourceGame = true,
        ) {
    }

    public function select(): ?Game
    {
        // First, find the main author of the source game's achievement set
        $author = Achievement::where('GameID', $this->sourceGame->id)
            ->where('Flags', AchievementFlag::OfficialCore->value)
            ->select('user_id')
            ->selectRaw('COUNT(*) as achievement_count')
            ->with(['developer:ID,User'])
            ->groupBy('user_id')
            ->orderByDesc('achievement_count')
            ->first();

        if (!$author) {
            return null;
        }

        $this->selectedAuthor = User::withTrashed()->find($author->user_id);

        // Then, find another game with achievements by this author
        $this->selectedGame = Game::query()
            ->whereNotIn('ConsoleID', System::getNonGameSystems())
            ->whereHas('achievements', function ($query) use ($author) {
                $query->where('user_id', $author->user_id)
                    ->where('Flags', AchievementFlag::OfficialCore->value);
            })
            ->where('ID', '!=', $this->sourceGame->id)
            ->whereHasPublishedAchievements()
            ->inRandomOrder()
            ->first();

        return $this->selectedGame;
    }

    public function reason(): GameSuggestionReason
    {
        return GameSuggestionReason::SharedAuthor;
    }

    public function reasonContext(): ?GameSuggestionContextData
    {
        if (!$this->selectedAuthor) {
            return null;
        }

        return GameSuggestionContextData::forSharedAuthor(
            UserData::from($this->selectedAuthor),
            sourceGame: $this->attachSourceGame
                ? GameData::fromGame($this->sourceGame)->include('badgeUrl')
                : null,
            sourceGameKind: $this->attachSourceGame ? $this->sourceGameKind : null,
        );
    }
}
