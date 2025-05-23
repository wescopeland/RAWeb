<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database\Eloquent\BaseModel;
use App\Support\Routing\HasSelfHealingUrls;
use Database\Factories\ForumFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Forum extends BaseModel
{
    /** @use HasFactory<ForumFactory> */
    use HasFactory;
    use HasSelfHealingUrls;
    use SoftDeletes;

    // TODO drop latest_comment_id -> derived
    protected $table = 'forums';

    protected $fillable = [
        'title',
        'description',
        'order_column',
    ];

    protected static function newFactory(): ForumFactory
    {
        return ForumFactory::new();
    }

    // == accessors

    public function getCanonicalUrlAttribute(): string
    {
        return route('forum.show', [$this, $this->getSlugAttribute()]);
    }

    public function getPermalinkAttribute(): string
    {
        return route('forum.show', $this);
    }

    // == mutators

    // == relations

    /**
     * @return BelongsTo<ForumCategory, Forum>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    /**
     * @return HasMany<ForumTopic>
     */
    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class, 'forum_id', 'id');
    }

    /**
     * @return HasManyThrough<ForumTopicComment>
     */
    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(ForumTopicComment::class, ForumTopic::class, 'forum_id', 'forum_topic_id');
    }
}
