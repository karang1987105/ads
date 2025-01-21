<?php

namespace App\Models;

use App\Models\Domains\PublisherDomain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Place
 *
 * @property int $id
 * @property string $title
 * @property int $domain_id
 * @property int $ad_type_id
 * @property string $uuid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by_id
 * @property-read \App\Models\AdType $adType
 * @property-read \App\Models\UserManager|null $approvedBy
 * @property-read PublisherDomain $domain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Record[] $records
 * @property-read int|null $records_count
 * @method static \App\Helpers\QueryBuilderHelper|Place approved()
 * @method static \App\Helpers\QueryBuilderHelper|Place newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Place newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Place page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Place query()
 * @method static \App\Helpers\QueryBuilderHelper|Place unapproved()
 * @method static \App\Helpers\QueryBuilderHelper|Place whereAdTypeId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Place whereApprovedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Place whereApprovedById($value)
 * @method static \App\Helpers\QueryBuilderHelper|Place whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Place whereDomainId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Place whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Place whereTitle($value)
 * @method static \App\Helpers\QueryBuilderHelper|Place whereUpdatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Place whereUuid($value)
 * @mixin \Eloquent
 */
class Place extends Model {
    use SoftDeletes;

    public $with = ['adType', 'domain'];
    public $casts = ['approved_at' => 'datetime'];
    public $fillable = ['title', 'domain_id', 'ad_type_id', 'approved_by_id', 'approved_at', 'uuid'];

    public function isApproved(): bool {
        return $this->approved_at != null;
    }

    public function adType(): BelongsTo {
        return $this->belongsTo(AdType::class, 'ad_type_id');
    }

    public function approvedBy(): BelongsTo {
        return $this->belongsTo(UserManager::class, 'approved_by_id', 'user_id');
    }

    public function domain(): BelongsTo {
        return $this->belongsTo(PublisherDomain::class);
    }

    public function records(): HasMany {
        return $this->hasMany(Record::class);
    }

    public function publisher() {
        return $this->domain->publisher;
    }

    public function invoices(): HasManyThrough {
        return $this->hasManyThrough(Invoice::class, InvoicePlace::class, 'place_id', 'id');
    }

    public function getUnpaidInvoice(Campaign $campaign) {
        return $this->invoices()->select(['invoices.*'])
            ->notPaid()
            ->where('invoices_places.campaign_id', '=', $campaign->id)
            ->whereNull('invoices.withdrawal_request_id')
            ->first();
    }

    public static function uuid($uuid) {
        return self::where('uuid', '=', $uuid)->first();
    }

    public function scopeApproved(Builder $builder): Builder {
        return $builder->whereNotNull('places.approved_at');
    }

    public function scopeUnapproved(Builder $builder): Builder {
        return $builder->whereNull('places.approved_at');
    }

    public function __get($key) {
        if ($key === 'publisher') {
            return $this->publisher();
        }
        return parent::__get($key);
    }

    public static function activePlacesCount(?User $user): int {
        return self::query()
            ->join('users_publishers_domains', 'users_publishers_domains.id', 'places.domain_id')
            ->whereNotNull('places.approved_at')
            ->whereNotNull('users_publishers_domains.approved_at')
            ->when($user, fn(Builder $query) => $query->where('users_publishers_domains.publisher_id', $user->id))
            ->count();
    }
    
    public static function activePlacesCountEachCountry(?User $user): array {
        $eachCountries = self::query()
                        ->selectRaw('users.country_id, count(places.id) AS countPlace')
                        ->join('users_publishers_domains', 'users_publishers_domains.id', 'places.domain_id')
                        ->join('users', 'users.id', 'users_publishers_domains.publisher_id')
                        ->whereNotNull('places.approved_at')
                        ->whereNotNull('users_publishers_domains.approved_at')
                        ->groupBy('users.country_id')
                        ->get();
        $placesCountEachCountry = [];
        foreach($eachCountries as $eachCountry) {
            $placesCountEachCountry[] = [
                'country_id' => $eachCountry->country_id,
                'count' => $eachCountry->countPlace
            ];
        }
        return $placesCountEachCountry;
    }
}
