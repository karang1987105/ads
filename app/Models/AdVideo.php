<?php

namespace App\Models;

use App\Models\Domains\AdvertiserDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\AdVideo
 *
 * @property int $ad_id
 * @property string $title
 * @property string $file
 * @property int $domain_id
 * @property string $url
 * @property-read \App\Models\Ad $ad
 * @property-read AdvertiserDomain $domain
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo query()
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo whereAdId($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo whereDomainId($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo whereFile($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo whereTitle($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdVideo whereUrl($value)
 * @mixin \Eloquent
 */
class AdVideo extends Model {
    use HasFactory;

    protected $table = 'ads_videos';
    protected $primaryKey = 'ad_id';
    public $incrementing = false;
    public $timestamps = false;
    public $fillable = ['title', 'file', /*'alt_text', */'domain_id', 'url'/*, 'loop'*/];

    public function ad(): Relations\BelongsTo {
        return $this->belongsTo(Ad::class);
    }

    public function domain(): Relations\BelongsTo {
        return $this->belongsTo(AdvertiserDomain::class);
    }
}
