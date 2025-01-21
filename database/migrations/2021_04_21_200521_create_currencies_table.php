<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateCurrenciesTable extends Migration {
    public function up() {
        AdsSchema::create('currencies', function (BlueprintHelper $table) {
            $table->char('id', 4, ascii: true)->primary();
            $table->string('name');
            $table->string('coingecko')->unique()->comment('coingecko coin id');
            $table->string('rpc_server')->comment('url of rpc server');
            $table->amount('exchange_rate', crypto: true)->comment('exchange rate by USD');
            $table->boolean('active');
            $table->percent('bonus')->nullable();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('currencies');
    }
}
