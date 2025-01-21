<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\Record
 *
 * @property int $id
 * @property int|null $campaign_id
 * @property int|null $place_id
 * @property string $country_id
 * @property string $cost
 * @property string $revenue
 * @property \Illuminate\Support\Carbon $time
 * @property-read \App\Models\Campaign|null $campaign
 * @property-read \App\Models\Place|null $place
 * @method static \App\Helpers\QueryBuilderHelper|Record newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Record newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Record page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Record query()
 * @method static \App\Helpers\QueryBuilderHelper|Record whereCampaignId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Record whereCost($value)
 * @method static \App\Helpers\QueryBuilderHelper|Record whereCountryId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Record whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Record wherePlaceId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Record whereRevenue($value)
 * @method static \App\Helpers\QueryBuilderHelper|Record whereTime($value)
 * @mixin \Eloquent
 */
class Record extends Model {
    use HasFactory;

    public $fillable = ['campaign_id', 'place_id', 'kind', 'country_id', 'cost', 'revenue', 'time'];
    public $casts = ['time' => 'datetime'];
    public $timestamps = false;

    public function campaign(): Relations\BelongsTo {
        return $this->belongsTo(Campaign::class);
    }

    public function ad(): Ad {
        return $this->campaign->ad;
    }

    public function place(): Relations\BelongsTo {
        return $this->belongsTo(Place::class);
    }
}