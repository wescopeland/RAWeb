<?php

declare(strict_types=1);

namespace App\Community\Actions;

use App\Community\Enums\UserGameListType;
use App\Models\Game;
use App\Models\User;

class RemoveGameFromListAction
{
    public function execute(User $user, Game $game, UserGameListType $type): bool
    {
        return $user->gameListEntries($type)->where('GameID', $game->ID)->delete() === 1;
    }
}
