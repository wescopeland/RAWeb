<?php

declare(strict_types=1);

namespace App\Platform\Models;

use App\Site\Models\User;
use App\Support\Database\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends BaseModel
{
    protected $table = 'rankings';

    protected $fillable = [
        'user_id',
        'system_id',
        'game_id',
        'type',
        'value',
        'updated_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'system_id' => 'integer',
        'game_id' => 'integer',
        'value' => 'integer',
    ];

    // == accessors

    // == mutators

    // == relations

    /**
     * @return BelongsTo<System, Ranking>
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class, 'system_id');
    }

    /**
     * @return BelongsTo<User, Ranking>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // == scopes
}
