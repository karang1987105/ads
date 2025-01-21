<?php

namespace App\Models;

use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * App\Models\TicketThread
 *
 * @property int $id
 * @property string $subject
 * @property string $category
 * @property bool $closed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TicketMessage|null $message
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketMessage[] $messages
 * @property-read int|null $messages_count
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread closed(bool $closed = true)
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread involvesMe()
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread query()
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread unanswered()
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread whereCategory($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread whereClosed($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread whereSubject($value)
 * @method static \App\Helpers\QueryBuilderHelper|TicketThread whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TicketThread extends Model {
    public $table = 'tickets_threads';
    public $fillable = ['subject', 'category', 'closed'];
    public $casts = ['closed' => 'boolean', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function messages(): HasMany {
        return $this->hasMany(TicketMessage::class, 'thread_id');
    }

    public function message(): HasOne {
        return $this->HasOne(TicketMessage::class, 'thread_id')->where('reply', '=', false);
    }

    public function scopeClosed(Builder $builder, bool $closed = true) {
        return $builder->where('tickets_threads.closed', '=', $closed);
    }

    public function scopeUnanswered(Builder $builder) {
        return $builder->has('messages', '=');
    }

    public function scopeInvolvesMe(Builder $builder) {
        return $builder->whereHas('messages', fn(Builder $q) => $q->where('user_id', '=', Auth::id()));
    }

    public function isGuestThread(): bool {
        return $this->message->guest !== null;
    }

    public static function deleteUserThreads(int $userId): int {
        return TicketThread::join('tickets_messages', function (JoinClause $q) use ($userId) {
            $q->whereColumn('tickets_messages.thread_id', '=', 'tickets_threads.id');
            $q->where('tickets_messages.reply', '=', false);
            $q->where('tickets_messages.user_id', '=', $userId);
        })->delete();
    }
}
