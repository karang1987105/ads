<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAdsRemoveAltText extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('ads_banners', function (Blueprint $table) {
            $table->dropColumn('alt_text');
        });

        Schema::table('ads_videos', function (Blueprint $table) {
            $table->dropColumn('alt_text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('ads_banners', function (Blueprint $table) {
            $table->string('alt_text', 30)->after('file');
        });

        Schema::table('ads_videos', function (Blueprint $table) {
            $table->string('alt_text', 30)->after('file');
        });
    }
}
