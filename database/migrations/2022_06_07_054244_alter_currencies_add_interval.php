<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCurrenciesAddInterval extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('currencies', function (Blueprint $table) {
            $table->integer('rpc_block_count_interval')->nullable()->after('rpc_block_count_ts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('rpc_block_count_interval');
        });
    }
}
