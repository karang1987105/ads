<?php

use App\Helpers\AdsSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCampaignTrackingTable extends Migration {
    public function up() {
        AdsSchema::create('campaigns_tracking', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('place_id');
            $table->ipAddress('ip');
            $table->integer('time');

            $table->foreign('campaign_id')->references('id')->on('campaigns')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('campaigns_tracking');
    }
}