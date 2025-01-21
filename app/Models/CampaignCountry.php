<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\CampaignCountry
 *
 * @property int $id
 * @property int $campaign_id
 * @property string|null $country_id NULL for Tier 4
 * @property-read \App\Models\Campaign $campaign
 * @property-read \App\Models\Country|null $country
 * @method static \App\Helpers\QueryBuilderHelper|CampaignCountry newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|CampaignCountry newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|CampaignCountry page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|CampaignCountry query()
 * @method static \App\Helpers\QueryBuilderHelper|CampaignCountry whereCampaignId($value)
 * @method static \App\Helpers\QueryBuilderHelper|CampaignCountry whereCountryId($value)
 * @method static \App\Helpers\QueryBuilderHelper|CampaignCountry whereId($value)
 * @mixin \Eloquent
 */
class CampaignCountry extends Model {
    use HasFactory;

    public $table = 'campaigns_countries';
    public $fillable = ['campaign_id', 'country_id'];
    public $timestamps = false;

    public function campaign(): Relations\BelongsTo {
        return $this->belongsTo(Campaign::class);
    }

    public function country(): Relations\BelongsTo {
        return $this->belongsTo(Country::class);
    }
}
