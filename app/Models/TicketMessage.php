<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\TicketMessage
 *
 * @property int $id
 * @property bool $reply
 * @property int $thread_id
 * @property int|null $user_id NULL for guest tickets
 * @property string|null $guest Guest's email address to reply
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TicketThread $thread
 * @property-read \App\Models\User|null $user
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage notReply()
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage query()
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage reply()
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage whereGuest($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage whereMessage($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage whereReply($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage whereThreadId($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage whereUpdatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketMessage whereUserId($value)
 * @mixin \Eloquent
 */
class TicketMessage extends Model {
    public $table = 'tickets_messages';
    public $fillable = ['reply', 'message', 'thread_id', 'user_id', 'guest'];
    public $casts = ['reply' => 'boolean', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function thread(): BelongsTo {
        return $this->belongsTo(TicketThread::class, 'thread_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function scopeNotReply(Builder $builder) {
        return $builder->where('reply', '=', false);
    }

    public function scopeReply(Builder $builder) {
        return $builder->where('reply', '=', true);
    }

    public function isGuestMessage(): bool {
        return $this->guest !== null;
    }
}
