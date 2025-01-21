<?php

namespace App\Models\Domains;

use App\Helpers\QueryBuilderHelper;
use App\Models\UserAdvertiser;
use App\Models\UserManager;
use Database\Factories\AdvertiserDomainFactory;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AdvertiserDomain extends UserDomain
{
    use HasFactory;

    public $fillable = ['advertiser_id', 'domain', 'approved_by_id', 'approved_at'];
    protected $table = "users_advertisers_domains";

    public function advertiser(): BelongsTo {
        return $this->belongsTo(UserAdvertiser::class, 'advertiser_id');
    }

    protected static function newFactory() {
        return AdvertiserDomainFactory::new();
    }

    public static function activeAdsJoin($userId = null) {
        return self::select([
            'users_advertisers_domains.id',
            DB::raw('COUNT(ads.id) AS cnt')
        ])
            ->leftjoin('ads_banners', 'ads_banners.domain_id', '=', 'users_advertisers_domains.id')
            ->leftjoin('ads_videos', 'ads_videos.domain_id', '=', 'users_advertisers_domains.id')
            ->leftjoin('ads', function ($j) {
                $j->whereNull('ads.deleted_at');
                $j->where(fn($jj) => $jj->whereColumn('ads.id', 'ads_banners.ad_id')->orWhereColumn('ads.id', 'ads_videos.ad_id'));
            })
            ->when($userId, fn(Builder $query) => $query->where('users_advertisers_domains.advertiser_id', $userId))
            ->groupBy('users_advertisers_domains.id');
    }

    public static function inUse(AdvertiserDomain $domain): bool {
        return self::activeAdsJoin()
                ->where('users_advertisers_domains.id', $domain->id)
                ->value('cnt') > 0;
    }
}