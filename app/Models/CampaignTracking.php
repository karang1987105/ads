<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CampaignTracking
 *
 * @property int $id
 * @property int $campaign_id
 * @property int $place_id
 * @property string $ip
 * @property int $time
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTracking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTracking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTracking query()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTracking whereCampaignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTracking whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTracking wherePlaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTracking whereTime($value)
 * @mixin \Eloquent
 */
class CampaignTracking extends Model {
    protected $table = 'campaigns_tracking';
    protected $fillable = ['id', 'campaign_id', 'place_id', 'ip', 'time'];
    public $timestamps = false;
}