<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder {
    public function run() {
        DB::table('currencies')->insert([
            ['id' => 'LTC', 'name' => 'Litecoin', 'bonus' => null, 'active' => true, 'coingecko' => 'litecoin', 'rpc_server' => 'http://45.156.23.145:9432/', 'exchange_rate' => 0.0055],
            ['id' => 'BTC', 'name' => 'Bitcoin', 'bonus' => null, 'active' => true, 'coingecko' => 'bitcoin', 'rpc_server' => 'http://127.0.0.1/', 'exchange_rate' => 0.000021],
            ['id' => 'ETH', 'name' => 'Ethereum', 'bonus' => null, 'active' => true, 'coingecko' => 'ethereum', 'rpc_server' => 'http://127.0.0.1/', 'exchange_rate' => 0.00029],
        ]);
    }
}
