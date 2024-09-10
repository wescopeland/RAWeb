<?php

declare(strict_types=1);

namespace App\Community\Data;

use App\Models\UserGameListEntry;
use App\Platform\Data\GameData;
use App\Platform\Data\PlayerGameData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript('UserGameListEntry')]
class UserGameListEntryData extends Data
{
    public function __construct(
        public int $id,
        public Lazy|GameData $game,
        public Lazy|PlayerGameData|null $playerGame,
    ) {
    }

    public static function fromModel(UserGameListEntry $userGameListEntry): self
    {
        $playerGame = $userGameListEntry->game->playerGames->first();

        return new self(
            id: $userGameListEntry->id,
            game: Lazy::create(fn () => GameData::fromGame($userGameListEntry->game)),
            playerGame: Lazy::create(fn () => $playerGame ? PlayerGameData::from($playerGame) : null),
        );
    }
}
