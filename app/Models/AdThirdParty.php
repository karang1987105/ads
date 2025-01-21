<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\AdThirdParty
 *
 * @property int $ad_id
 * @property string $title
 * @property string $code
 * @property-read \App\Models\Ad $ad
 * @method static \App\Helpers\QueryBuilderHelper|AdThirdParty newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|AdThirdParty newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|AdThirdParty page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|AdThirdParty query()
 * @method static \App\Helpers\QueryBuilderHelper|AdThirdParty whereAdId($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdThirdParty whereCode($value)
 * @method static \App\Helpers\QueryBuilderHelper|AdThirdParty whereTitle($value)
 * @mixin \Eloquent
 */
class AdThirdParty extends Model {
    use HasFactory;

    protected $table = 'ads_thirdparties';
    protected $primaryKey = 'ad_id';
    public $incrementing = false;
    public $timestamps = false;
    public $fillable = ['title', 'code'];

    public function ad(): Relations\BelongsTo {
        return $this->belongsTo(Ad::class);
    }
}
