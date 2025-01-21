<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAdsTypesAddDevice extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('ads_types', function (Blueprint $table) {
            $table->enum('device', ['Mobile', 'Desktop', 'All'])->default('All')->after('kind');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('ads_types', function (Blueprint $table) {
            $table->dropColumn('device');
        });
    }
}
