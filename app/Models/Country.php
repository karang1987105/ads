<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;


/**
 * App\Models\Country
 *
 * @property string $id
 * @property string $name
 * @property int $hidden
 * @property string $category
 * @property int $utc_start
 * @method static \App\Helpers\QueryBuilderHelper|Country newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Country newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Country page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Country query()
 * @method static \App\Helpers\QueryBuilderHelper|Country tier($tier)
 * @method static \App\Helpers\QueryBuilderHelper|Country tier1()
 * @method static \App\Helpers\QueryBuilderHelper|Country tier2()
 * @method static \App\Helpers\QueryBuilderHelper|Country tier3()
 * @method static \App\Helpers\QueryBuilderHelper|Country tier4()
 * @method static \App\Helpers\QueryBuilderHelper|Country visible()
 * @method static \App\Helpers\QueryBuilderHelper|Country whereCategory($value)
 * @method static \App\Helpers\QueryBuilderHelper|Country whereHidden($value)
 * @method static \App\Helpers\QueryBuilderHelper|Country whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Country whereName($value)
 * @method static \App\Helpers\QueryBuilderHelper|Country whereUtcStart($value)
 * @mixin \Eloquent
 */
class Country extends Model {
    public $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;
    public $fillable = ['hidden', 'category'];

    public function scopeVisible(Builder $query): Builder {
        return $query->where('countries.hidden', '=', false);
    }

    public function scopeTier(Builder $query, $tier): Builder {
        return $query->where('category', '=', $tier);
    }

    public function scopeTier1(Builder $query): Builder {
        return $query->where('category', '=', 'Tier 1');
    }

    public function scopeTier2(Builder $query): Builder {
        return $query->where('category', '=', 'Tier 2');
    }

    public function scopeTier3(Builder $query): Builder {
        return $query->where('category', '=', 'Tier 3');
    }

    public function scopeTier4(Builder $query): Builder {
        return $query->where('category', '=', 'Tier 4');
    }

    public static function getCodeByIp(string $ip): Country|null {
        $ip = ip2long($ip);
        $id = DB::table("countries_ips")
            ->where("start", '<=', $ip)
            ->where('end', '>=', $ip)
            ->first('country')
            ->country;
        $code = $id !== 'ZZ' ? self::find($id) : null;
        return $code;
    }

    public function getCurrentTimestamp() {
        return ((int)gmdate('U')) + $this->utc_start;
    }
}
