<?php

declare(strict_types=1);

namespace App\Platform\Actions;

use App\Models\GameSet;
use App\Models\System;
use App\Platform\Data\GameSetData;
use App\Platform\Enums\GameSetType;
use Illuminate\Support\Collection;

class BuildGameSetRelatedHubsAction
{
    /**
     * Get both parent and child hubs for a game set, de-duped by ID.
     *
     * @return GameSetData[]
     */
    public function execute(GameSet $gameSet): array
    {
        $childHubs = $this->getHubsByRelation($gameSet, 'parents');
        $parentHubs = $this->getHubsByRelation($gameSet, 'children');

        // Merge and dedupe by ID.
        return $childHubs->concat($parentHubs)
            ->unique('id')
            ->sortBy('title')
            ->values()
            ->all();
    }

    /**
     * Get hubs based on the specified relationship (parents or children).
     *
     * @return Collection<int, GameSetData>
     */
    private function getHubsByRelation(GameSet $gameSet, string $relation): Collection
    {
        return GameSet::whereHas($relation, function ($query) use ($gameSet, $relation) {
            $query->where($relation === 'parents' ? 'parent_game_set_id' : 'child_game_set_id', $gameSet->id);
        })
            ->whereType(GameSetType::Hub)
            ->select([
                'id',
                'title',
                'image_asset_path',
                'type',
                'has_mature_content',
                'updated_at',
            ])
            ->withCount([
                'games' => function ($query) {
                    $query->whereNull('GameData.deleted_at')
                        ->where('GameData.ConsoleID', '!=', System::Hubs);
                },
                'parents as link_count' => function ($query) {
                    $query->whereNull('game_sets.deleted_at');
                },
            ])
            ->orderBy('title')
            ->get()
            ->map(fn (GameSet $hub) => GameSetData::fromGameSetWithCounts($hub));
    }
}
