<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCampaignAddNotification extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->boolean('notification_sent')->nullable(false)->after('impressions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('notification_sent');
        });
    }
}
