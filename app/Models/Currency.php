<?php

namespace App\Models;

use App\Services\Currency\CurrencyService;
use DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Currency
 *
 * @property string $id
 * @property string $name
 * @property string $coingecko coingecko coin id
 * @property string $rpc_server url of rpc server
 * @property int $rpc_block_count
 * @property int $rpc_block_count_ts
 * @property int|null $rpc_block_count_interval
 * @property string $exchange_rate exchange rate by USD
 * @property bool $active
 * @property string|null $bonus
 * @method static \App\Helpers\QueryBuilderHelper|Currency active()
 * @method static \App\Helpers\QueryBuilderHelper|Currency available()
 * @method static \App\Helpers\QueryBuilderHelper|Currency newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Currency newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Currency page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Currency query()
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereActive($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereBonus($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereCoingecko($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereExchangeRate($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereName($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereRpcBlockCount($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereRpcBlockCountInterval($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereRpcBlockCountTs($value)
 * @method static \App\Helpers\QueryBuilderHelper|Currency whereRpcServer($value)
 * @mixin \Eloquent
 */
class Currency extends Model {
    public $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ['id', 'name', 'active', 'bonus', 'coingecko', 'rpc_server', 'rpc_block_count', 'rpc_block_count_ts',
        'rpc_block_count_interval', 'exchange_rate'];
    protected $casts = ['active' => 'boolean'];

    public function scopeActive(Builder $builder): Builder {
        return $builder->where('active', '=', true);
    }

    public function scopeAvailable(Builder $builder): Builder {
        if (app()->environment('local')) {
            return $builder;
        }

        return $builder->where('rpc_block_count_ts', '>', DB::raw('UNIX_TIMESTAMP() - rpc_block_count_interval'));
    }

    public function isAvailable(): bool {
        if (app()->environment('local')) {
            return true;
        }
        return $this->rpc_block_count_ts > time() - $this->rpc_block_count_interval;
    }

    public function getService() {
        $service = "App\Services\Currency\\" . strtoupper($this->id) . "Service";
        return class_exists($service) ? new $service($this) : new CurrencyService($this);
    }

    public function getWallet(int|User $user, bool $generateIfNotExists = true): string|null {
        if (config('app.debug')) {
            return $this->id . '_TEST';
        }
        $currencyService = $this->getService();
        $label = $currencyService->getLabel($user instanceof User ? $user : User::find($user));
        return $currencyService->getWallet($label, $generateIfNotExists);
    }

    public function verifyTransaction(int|User $user, float $amount): ?string {
        $service = $this->getService();
        $label = $service->getLabel($user instanceof User ? $user : User::find($user));
        return $service->verifyTransaction($label, $amount);
    }
}
