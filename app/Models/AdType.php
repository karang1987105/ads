<?php

namespace App\Models;

use Arr;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * App\Models\AdType
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $kind
 * @property string $device
 * @property int $width
 * @property int $height
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ad[] $ads
 * @property-read int|null $ads_count
 * @method static \App\Helpers\QueryBuilderHelper|AdType active()
 * @method static \App\Helpers\QueryBuilderHelper|AdType newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|AdType newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|AdType page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|AdType query()
 * @method static \App\Helpers\QueryBuilderHelper|AdType type(array|string $type)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereActive($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereDevice($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereHeight($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereKind($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereName($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereType($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereUpdatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdType whereWidth($value)
 * @mixin \Eloquent
 */
class AdType extends Model {
    protected $table = 'ads_types';
    protected $fillable = ['id', 'name', 'active', 'type', 'kind', 'device', 'width', 'height'];
    protected $casts = ['active' => 'boolean'];

    public function ads(): HasMany {
        return $this->hasMany(Ad::class, 'ad_type_id');
    }

    public function isBanner(): bool {
        return $this->type === 'Banner';
    }

    public function isVideo(): bool {
        return $this->type === 'Video';
    }

    public function getSize(): string {
        return $this->width . 'x' . $this->height;
    }

    public function scopeActive(Builder $builder): Builder {
        return $builder->where('active', true);
    }

    public function scopeType(Builder $builder, string|array $type): Builder {
        return $builder->whereIn('type', Arr::wrap($type));
    }

    public function isCPC(): bool {
        return $this->kind === 'CPC';
    }

    public function isCPM(): bool {
        return $this->kind === 'CPM';
    }

    public function isCPV(): bool {
        return $this->kind === 'CPV';
    }

    public function firstCampaignOnQueue(int $categoryId, string $countryCode, int $placeId, string $ip, bool $isMobile): Collection {
        $col = strtolower($this->kind);
        $mileSize = $col === 'cpm' ? config('ads.cpm_mile_size') : 1;
        $adSubTable = 'ads_' . strtolower($this->type) . 's';

        $query = Campaign::query()
            ->select(['campaigns.id', 'campaigns.proxy'])
            ->join('ads', 'ads.id', 'campaigns.ad_id')
            ->join('users', function ($j) {
                $j->whereRaw('IF(ads.advertiser_id IS NOT NULL, users.id=ads.advertiser_id, users.id=?)')->addBinding(User::admin()->id);
                $j->whereNotNull('users.active');
            })
            ->join('campaigns_countries', function ($j) use ($countryCode) {
                $j->whereColumn('campaigns_countries.campaign_id', '=', 'campaigns.id');
                $j->where('campaigns_countries.country_id', '=', $countryCode);
            })
            ->join('categories', 'categories.id', '=', 'campaigns.category_id')
            ->leftjoin('categories_countries', function ($j) use ($countryCode) {
                $j->whereColumn('categories_countries.category_id', '=', 'categories.id');
                $j->where('categories_countries.country_id', '=', $countryCode);
            })
            ->joinSub('SELECT campaign_id, SUM(amount-current) AS balance FROM invoices_campaigns GROUP BY campaign_id',
                'invoices_campaigns', 'invoices_campaigns.campaign_id', '=', 'campaigns.id')
            ->leftjoin('campaigns_tracking', function ($j) use ($ip, $placeId) {
                $j->whereColumn('campaigns_tracking.campaign_id', '=', 'campaigns.id');
                $j->where('campaigns_tracking.place_id', '=', $placeId);
                $j->where('campaigns_tracking.ip', '=', $ip);
                $j->where('campaigns_tracking.time', '>', (time() - config('ads.ads.throttle') * 60));
            })
            ->leftjoin($adSubTable, $adSubTable . '.ad_id', 'ads.id')
            ->leftjoin('users_advertisers_domains', function ($j) use ($adSubTable) {
                $j->whereColumn('users_advertisers_domains.id', '=', $adSubTable . '.domain_id');
                $j->whereNotNull('users_advertisers_domains.approved_at');
            })
            ->where(fn($q) => $q->where('ads.is_third_party', true)->orWhereNotNull('users_advertisers_domains.id'))
            ->where('ads.ad_type_id', '=', $this->id)
            ->whereNotNull('ads.approved_at')
            ->where('campaigns.category_id', '=', $categoryId)
            ->whereNull('campaigns.stopped_at')
            ->where('campaigns.enabled', '=', true)
            ->where('campaigns.device', '!=', $isMobile ? 'Desktop' : 'Mobile')
            ->whereNull('campaigns_tracking.id')
            ->where('invoices_campaigns.balance', '>=', DB::raw("ROUND(IFNULL(categories_countries.$col, categories.$col) / $mileSize, 9)"))
            ->groupBy(['campaigns.id', 'campaigns.proxy'])

            // Priority: Normal ads, Admin ads, third party ads
            ->orderBy('campaigns.impressions')
            ->orderByRaw('(ads.is_third_party=0 AND ads.advertiser_id IS NOT NULL) DESC')
            ->orderBy('ads.is_third_party')
            ->orderByRaw('RAND()')
            ->limit(1);

        $query1 = $query->clone();
        $query2 = $query->clone()->where('campaigns.proxy', true);
        $result = $query1->union($query2);

        //\Log::info(\App\Helpers\Helper::dump($result));

        /** @noinspection StaticInvocationViaThisInspection */
        return $result->get();
    }
}
