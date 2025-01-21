<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * App\Models\Ad
 *
 * @property int $id
 * @property int $ad_type_id
 * @property int|null $advertiser_id Null for admin
 * @property bool $is_third_party
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by_id
 * @property-read \App\Models\AdType $adType
 * @property-read \App\Models\UserAdvertiser|null $advertiser
 * @property-read \App\Models\UserManager|null $approvedBy
 * @property-read \App\Models\AdBanner|null $banner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Campaign[] $campaigns
 * @property-read int|null $campaigns_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Record[] $records
 * @property-read int|null $records_count
 * @property-read \App\Models\AdThirdParty|null $thirdParty
 * @property-read \App\Models\AdVideo|null $video
 * @method static \App\Helpers\QueryBuilderHelper|Ad approved()
 * @method static \App\Helpers\QueryBuilderHelper|Ad asBanner()
 * @method static \App\Helpers\QueryBuilderHelper|Ad asNormalAd()
 * @method static \App\Helpers\QueryBuilderHelper|Ad asThirdParty()
 * @method static \App\Helpers\QueryBuilderHelper|Ad asVideo()
 * @method static \App\Helpers\QueryBuilderHelper|Ad newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Ad newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Ad page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Ad query()
 * @method static \App\Helpers\QueryBuilderHelper|Ad unapproved()
 * @method static \App\Helpers\QueryBuilderHelper|Ad whereAdTypeId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Ad whereAdvertiserId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Ad whereApprovedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Ad whereApprovedById($value)
 * @method static \App\Helpers\QueryBuilderHelper|Ad whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Ad whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Ad whereIsThirdParty($value)
 * @method static \App\Helpers\QueryBuilderHelper|Ad whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ad extends Model {
    use HasFactory, SoftDeletes;

    public $with = ['adType', 'advertiser.user:id,name', 'banner.domain:id,domain,approved_at', 'video.domain:id,domain,approved_at'];
    public $casts = ['approved_at' => 'datetime', 'is_third_party' => 'boolean'];
    public $fillable = ['ad_type_id', 'advertiser_id', 'approved_by_id', 'approved_at', 'is_third_party'];

    public function isApproved(): bool {
        return $this->approved_at != null;
    }

    public function adType(): Relations\BelongsTo {
        return $this->belongsTo(AdType::class, 'ad_type_id');
    }

    public function approvedBy(): Relations\BelongsTo {
        return $this->belongsTo(UserManager::class, 'approved_by_id', 'user_id');
    }

    public function advertiser(): Relations\BelongsTo {
        return $this->belongsTo(UserAdvertiser::class, 'advertiser_id', 'user_id');
    }

    public function banner(): Relations\HasOne {
        return $this->hasOne(AdBanner::class);
    }

    public function video(): Relations\HasOne {
        return $this->hasOne(AdVideo::class);
    }

    public function thirdParty(): Relations\HasOne {
        return $this->hasOne(AdThirdParty::class);
    }

    public function campaigns(): Relations\HasMany {
        return $this->hasMany(Campaign::class);
    }

    public function records(): Relations\HasManyThrough {
        return $this->hasManyThrough(Record::class, Campaign::class, 'ad_id', 'campaign_id');
    }

    public function getType(): string {
        return $this->adType->type;
    }

    public function isBanner(): bool {
        return $this->adType->isBanner();
    }

    public function isVideo(): bool {
        return $this->adType->isVideo();
    }

    public function isThirdParty(): bool {
        return $this->is_third_party === true;
    }

    public function isAdminsAd(): bool {
        return $this->advertiser_id === null;
    }

    public function isNormalAd(): bool {
        return !$this->isAdminsAd() && !$this->isThirdParty();
    }

    public function getUrl($withDomain = false): ?string {
        if (!$this->isThirdParty()) {
            if ($this->isBanner()) {
                return ($withDomain ? $this->banner->domain->domain : '') . $this->banner->url;
            } else {
                return ($withDomain ? $this->video->domain->domain : '') . $this->video->url;
            }
        }
        return null;
    }

    public function getTitle(): string {
        return !$this->isThirdParty() ? ($this->isBanner() ? $this->banner->title : $this->video->title) : $this->thirdParty->title;
    }

//    public function getAltText(): string {
//        return !$this->isThirdParty() ? ($this->isBanner() ? $this->banner->alt_text : $this->video->alt_text) : '';
//    }

    public function getDomain($idOnly = false) {
        $property = $idOnly ? 'domain_id' : 'domain';
        return !$this->isThirdParty() ? ($this->isBanner() ? $this->banner->$property : $this->video->$property) : null;
    }

    public function getFilePath(): ?string {
        return !$this->isThirdParty() ? ($this->isBanner() ? $this->banner->file : $this->video->file) : null;
    }

    public function hasFile(): bool {
        return !$this->isThirdParty();
    }

    public function scopeApproved(Builder $builder): Builder {
        return $builder->whereNotNull('approved_at');
    }

    public function scopeUnapproved(Builder $builder): Builder {
        return $builder->whereNull('approved_at');
    }

    public function scopeAsBanner(Builder $builder): Builder {
        return $builder->join('ads_types', fn($q) => $q->where('ads_types.type', '=', 'Banner'));
    }

    public function scopeAsVideo(Builder $builder): Builder {
        return $builder->join('ads_types', fn($q) => $q->where('ads_types.type', '=', 'Video'));
    }

    public function scopeAsThirdParty(Builder $builder): Builder {
        return $builder->where('is_third_party', '=', true);
    }

    public function scopeAsNormalAd(Builder $builder): Builder {
        return $builder->where('is_third_party', '=', false);
    }
}