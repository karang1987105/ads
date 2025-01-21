<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * App\Models\Campaign
 *
 * @property int $id
 * @property int $ad_id
 * @property string $device
 * @property string $revenue_ratio
 * @property bool $enabled For Advertiser to stop/start the campaign.
 * @property bool $proxy Disable to exclude vpn/proxy visits
 * @property int $category_id
 * @property string $uuid
 * @property int $impressions
 * @property int $notification_sent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $stopped_at For manager/system to stop the campaign.
 * @property int|null $stopped_by_id If stopped_at has value nut stopped_by is NULL. Stopped by System can't be started
 * @property-read \App\Models\Ad $ad
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CampaignCountry[] $countries
 * @property-read int|null $countries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceCampaign[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Record[] $records
 * @property-read int|null $records_count
 * @property-read \App\Models\UserManager|null $stoppedBy
 * @method static \App\Helpers\QueryBuilderHelper|Campaign active()
 * @method static \App\Helpers\QueryBuilderHelper|Campaign adApproved()
 * @method static \App\Helpers\QueryBuilderHelper|Campaign enabled()
 * @method static \App\Helpers\QueryBuilderHelper|Campaign newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Campaign newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Campaign page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Campaign query()
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereAdId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereCategoryId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereDevice($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereEnabled($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereImpressions($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereNotificationSent($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereProxy($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereRevenueRatio($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereStoppedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereStoppedById($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereUpdatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Campaign whereUuid($value)
 * @mixin \Eloquent
 */
class Campaign extends Model {
    use HasFactory, SoftDeletes;

    public $fillable = ['device', 'revenue_ratio', 'enabled', 'proxy', 'stopped_at', 'stopped_by_id', 'category_id', 'uuid', 'impressions', 'notification_sent'];
    public $casts = ['enabled' => 'boolean', 'proxy' => 'boolean', 'stopped_at' => 'datetime'];

    public function ad(): Relations\BelongsTo {
        return $this->belongsTo(Ad::class);
    }

    public function countries(): Relations\HasMany {
        return $this->hasMany(CampaignCountry::class)->with('country');
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function invoices(): Relations\HasMany {
        return $this->hasMany(InvoiceCampaign::class)->with('invoice');
    }

    public function records(): Relations\HasMany {
        return $this->hasMany(Record::class);
    }

    public function getUnbalancedInvoices(): Collection {
        return $this->invoices()->unbalanced()->get();
    }

    public function getBalance(): float {
        return round($this->invoices->sum(fn(InvoiceCampaign $ic) => $ic->getBalance()), 9);
    }

    public function isActive() {
        return $this->stopped_at === null;
    }

    public function stoppedBy(): BelongsTo {
        return $this->belongsTo(UserManager::class, 'stopped_by_id', 'user_id');
    }

    public static function uuid($uuid) {
        return self::where('uuid', '=', $uuid)->first();
    }

    public function impressed(int $placeId, string $ip, bool $impression, bool $track) {
        if ($impression) {
            // TODO rename the column
            $this->update([
                'impressions' => time()
            ]);
        }

        if ($track) {
            if (!config('app.debug')) {
                CampaignTracking::create(['campaign_id' => $this->id, 'place_id' => $placeId, 'ip' => $ip, 'time' => time()]);
            }
        }
    }

    public function isAvailableInCountry(string $countryCode): bool {
        return $this->countries()->where('country_id', '=', $countryCode)->exists();
    }

    public function getAdKind(): string {
        return $this->ad->adType->kind;
    }

    public function isCPC(): bool {
        return $this->getAdKind() === 'CPC';
    }

    public function isCPM(): bool {
        return $this->getAdKind() === 'CPM';
    }

    public function isCPV(): bool {
        return $this->getAdKind() === 'CPV';
    }

    public function scopeEnabled(Builder $builder) {
        return $builder->whereNotNull('enabled');
    }

    public function scopeActive(Builder $builder) {
        return $builder->whereNull('stopped_at');
    }

    public function scopeAdApproved(Builder $builder) {
        return $builder->whereHas('ad', fn($q) => $q->whereNotNull('approved_at'));
    }
}
