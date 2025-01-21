<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAdvertisersDomainsIndex extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users_advertisers_domains', function (Blueprint $table) {
            $table->dropUnique(['domain']);
            $table->unique(['domain', 'advertiser_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users_advertisers_domains', function (Blueprint $table) {
            $table->unique('domain');
            $table->dropUnique(['domain', 'advertiser_id']);
        });
    }
}
