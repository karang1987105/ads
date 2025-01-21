<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\CategoryCountry
 *
 * @property int $id
 * @property int $category_id
 * @property string $country_id
 * @property string|null $cpm
 * @property string|null $cpc
 * @property string|null $cpv
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Country $country
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry query()
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry tier(string $tier)
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry whereCategoryId($value)
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry whereCountryId($value)
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry whereCpc($value)
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry whereCpm($value)
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry whereCpv($value)
 * @method static \App\Helpers\QueryBuilderHelper|CategoryCountry whereId($value)
 * @mixin \Eloquent
 */
class CategoryCountry extends Model {
    protected $table = 'categories_countries';
    public $fillable = ['category_id', 'country_id', 'cpc', 'cpm', 'cpv'];
    public $timestamps = false;

    public function category(): Relations\BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function country(): Relations\BelongsTo {
        return $this->belongsTo(Country::class);
    }

    public function scopeTier(Builder $query, string $tier): Builder {
        return $query->where('country.category', '=', $tier);
    }
}
