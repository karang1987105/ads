<?php

namespace App\Models;

use App\Models\Domains\AdvertiserDomain;
use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\UserAdvertiser
 *
 * @property int $user_id
 * @property-read Collection|\App\Models\Ad[] $ads
 * @property-read int|null $ads_count
 * @property-read Collection|\App\Models\Campaign[] $campaigns
 * @property-read int|null $campaigns_count
 * @property-read Collection|AdvertiserDomain[] $domains
 * @property-read int|null $domains_count
 * @property-read \App\Models\User $user
 * @method static \App\Helpers\QueryBuilderHelper|UserAdvertiser active()
 * @method static \App\Helpers\QueryBuilderHelper|UserAdvertiser inactive()
 * @method static \App\Helpers\QueryBuilderHelper|UserAdvertiser newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|UserAdvertiser newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|UserAdvertiser page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|UserAdvertiser query()
 * @method static \App\Helpers\QueryBuilderHelper|UserAdvertiser whereUserId($value)
 * @mixin \Eloquent
 */
class UserAdvertiser extends Model {
    use HasUser;

    protected $table = 'users_advertisers';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;
    public $with = ['user'];

    public function domains(): Relations\HasMany {
        return $this->hasMany(AdvertiserDomain::class, 'advertiser_id');
    }

    public function ads(): Relations\HasMany {
        return $this->hasMany(Ad::class, 'advertiser_id');
    }

    public function campaigns(): Relations\HasManyThrough {
        return $this->hasManyThrough(Campaign::class, Ad::class, 'advertiser_id', 'ad_id');
    }

    public function invoices(bool|null $paid = null): Relations\HasMany {
        $hasMany = $this->hasMany(Invoice::class, 'user_id');
        return isset($paid) ? ($paid ? $hasMany->paid() : $hasMany->notPaid()) : $hasMany;
    }

    public function getBalance(): float|int {
        return $this->getUnbalancedInvoices()->sum('balance');
    }

    public function getUnbalancedInvoices(): Collection {
        return $this->invoices(true)
            ->select(['invoices.*'])
            ->withSum('campaigns', 'amount')
            ->get()
            ->filter(fn(Invoice $i) => $i->amount > ($i->campaigns_sum_amount ?? 0) || $i->amount < 0)
            ->each(function ($i) {
                return $i->balance = $i->amount - $i->campaigns_sum_amount; // balance
            });
    }

    public function scopeActive(Builder $query): Builder {
        return $query->join('users', fn($join) => $join->on('users.id', '=', 'users_advertisers.user_id')->whereNotNull('users.active'));
    }

    public function scopeInactive(Builder $query): Builder {
        return $query->join('users', fn($join) => $join->on('users.id', '=', 'users_advertisers.user_id')->whereNull('users.active'));
    }

    public function isActive(): bool {
        return $this->user->active !== null;
    }
}
