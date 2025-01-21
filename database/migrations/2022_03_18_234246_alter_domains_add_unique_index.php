<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDomainsAddUniqueIndex extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users_advertisers_domains', function (Blueprint $table) {
            $table->unique('domain');
        });
        Schema::table('users_publishers_domains', function (Blueprint $table) {
            $table->unique('domain');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users_advertisers_domains', function (Blueprint $table) {
            $table->dropIndex('domain');
        });
        Schema::table('users_publishers_domains', function (Blueprint $table) {
            $table->dropIndex('domain');
        });
    }
}
