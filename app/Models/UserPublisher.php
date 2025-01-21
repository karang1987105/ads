<?php

namespace App\Models;

use App\Models\Domains\PublisherDomain;
use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\UserPublisher
 *
 * @property int $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection|PublisherDomain[] $domains
 * @property-read int|null $domains_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Place[] $places
 * @property-read int|null $places_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WithdrawalRequest[] $withdrawalRequests
 * @property-read int|null $withdrawal_requests_count
 * @method static \App\Helpers\QueryBuilderHelper|UserPublisher active()
 * @method static \App\Helpers\QueryBuilderHelper|UserPublisher inactive()
 * @method static \App\Helpers\QueryBuilderHelper|UserPublisher newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|UserPublisher newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|UserPublisher page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|UserPublisher query()
 * @method static \App\Helpers\QueryBuilderHelper|UserPublisher whereUserId($value)
 * @mixin \Eloquent
 */
class UserPublisher extends Model {
    use HasUser;

    protected $table = 'users_publishers';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;
    public $with = ['user'];

    public function domains(): Relations\HasMany {
        return $this->hasMany(PublisherDomain::class, 'publisher_id');
    }

    public function places(): Relations\HasManyThrough {
        return $this->hasManyThrough(Place::class, PublisherDomain::class, 'publisher_id', 'domain_id', secondLocalKey: 'id');
    }

    public function withdrawalRequests(): Relations\HasMany {
        return $this->hasMany(WithdrawalRequest::class, 'user_id', 'user_id');
    }

    public function invoices(bool|null $paid = null): Relations\HasMany {
        $hasMany = $this->hasMany(Invoice::class, 'user_id');
        return isset($paid) ? ($paid ? $hasMany->paid() : $hasMany->notPaid()) : $hasMany;
    }

    public function getBalance() {
        return $this->invoices(false)->sum('invoices.amount');
    }

    public function scopeActive(Builder $query): Builder {
        return $query->join('users', fn($join) => $join->on('users.id', '=', 'users_publishers.user_id')->whereNotNull('users.active'));
    }

    public function scopeInactive(Builder $query): Builder {
        return $query->join('users', fn($join) => $join->on('users.id', '=', 'users_publishers.user_id')->whereNull('users.active'));
    }

    public function isActive(): bool {
        return $this->user->active !== null;
    }
}
