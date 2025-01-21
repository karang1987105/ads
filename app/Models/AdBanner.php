<?php

namespace App\Models;

use App\Models\Domains\AdvertiserDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\AdBanner
 *
 * @property int $ad_id
 * @property string $title
 * @property string $file
 * @property int $domain_id
 * @property string $url
 * @property-read \App\Models\Ad $ad
 * @property-read AdvertiserDomain $domain
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner query()
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner whereAdId($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner whereDomainId($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner whereFile($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner whereTitle($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdBanner whereUrl($value)
 * @mixin \Eloquent
 */
class AdBanner extends Model {
    use HasFactory;

    protected $table = 'ads_banners';
    protected $primaryKey = 'ad_id';
    public $incrementing = false;
    public $timestamps = false;
    public $fillable = ['title', 'file', /*'alt_text', */'domain_id', 'url'];

    public function ad(): Relations\BelongsTo {
        return $this->belongsTo(Ad::class);
    }

    public function domain(): Relations\BelongsTo {
        return $this->belongsTo(AdvertiserDomain::class);
    }
}
