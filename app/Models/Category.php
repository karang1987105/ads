<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $title
 * @property string $cpm
 * @property string $cpc
 * @property string $cpv
 * @property string $revenue_share
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategoryCountry[] $countries
 * @property-read int|null $countries_count
 * @method static \App\Helpers\QueryBuilderHelper|Category active()
 * @method static \App\Helpers\QueryBuilderHelper|Category newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Category newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Category page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Category query()
 * @method static \App\Helpers\QueryBuilderHelper|Category whereActive($value)
 * @method static \App\Helpers\QueryBuilderHelper|Category whereCpc($value)
 * @method static \App\Helpers\QueryBuilderHelper|Category whereCpm($value)
 * @method static \App\Helpers\QueryBuilderHelper|Category whereCpv($value)
 * @method static \App\Helpers\QueryBuilderHelper|Category whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Category whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Category whereRevenueShare($value)
 * @method static \App\Helpers\QueryBuilderHelper|Category whereTitle($value)
 * @method static \App\Helpers\QueryBuilderHelper|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model {
    public $fillable = ['title', 'cpm', 'cpc', 'cpv', 'revenue_share', 'active'];

    public function countries(): Relations\HasMany {
        return $this->hasMany(CategoryCountry::class)->with('country');
    }

    public function getCountry(string $countryCode) {
        return $this->countries()->where('country_id', '=', $countryCode)->first();
    }

    public function scopeActive(Builder $builder) {
        return $builder->where('active', '=', true);
    }
}
