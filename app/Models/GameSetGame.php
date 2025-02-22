<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database\Eloquent\BasePivot;
use Database\Factories\GameSetGameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameSetGame extends BasePivot
{
    /** @use HasFactory<GameSetGameFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'game_set_games';

    protected $fillable = [
        'game_set_id',
        'game_id',
        'created_at',
        'updated_at',
    ];

    protected static function newFactory(): GameSetGameFactory
    {
        return GameSetGameFactory::new();
    }

    // == accessors

    // == mutators

    // == relations

    /**
     * @return BelongsTo<GameSet, GameSetGame>
     */
    public function gameSet(): BelongsTo
    {
        return $this->belongsTo(GameSet::class, 'game_set_id');
    }

    /**
     * @return BelongsTo<Game, GameSetGame>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'ID');
    }

    // == scopes
}
