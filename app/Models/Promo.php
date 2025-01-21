<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Promo
 *
 * @property int $id
 * @property string $title
 * @property string $code
 * @property string $bonus
 * @property int|null $total Null means unlimited
 * @property int $purchased Increments on using promo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\PromoFactory factory(...$parameters)
 * @method static \App\Helpers\QueryBuilderHelper|Promo newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Promo newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Promo page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Promo query()
 * @method static \App\Helpers\QueryBuilderHelper|Promo whereBonus($value)
 * @method static \App\Helpers\QueryBuilderHelper|Promo whereCode($value)
 * @method static \App\Helpers\QueryBuilderHelper|Promo whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Promo whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Promo wherePurchased($value)
 * @method static \App\Helpers\QueryBuilderHelper|Promo whereTitle($value)
 * @method static \App\Helpers\QueryBuilderHelper|Promo whereTotal($value)
 * @method static \App\Helpers\QueryBuilderHelper|Promo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Promo extends Model {
    use HasFactory;

    protected $fillable = ['title', 'code', 'total', 'bonus'];

    public static function verifyCode($code): Promo|null {
        return Promo::where('code', '=', $code)
            ->where(fn($q) => $q->whereNull('total')->orWhereColumn('total', '>', 'purchased'))
            ->first();
    }

    public static function purchaseCode($code, $amount = 1) {
        $promo = self::verifyCode($code);
        if ($promo !== null) {
            return $promo->purchase($amount);
        }
    }

    public function purchase($amount = 1) {
        return $this->increment('purchased', $amount);
    }
}
