<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCurrenciesAddBlockInfo extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('currencies', function (Blueprint $table) {
            $table->integer('rpc_block_count')->default(0)->after('rpc_server');
            $table->integer('rpc_block_count_ts')->default(0)->after('rpc_block_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn(['rpc_block_count', 'rpc_block_count_ts']);
        });
    }
}
