<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateAdsHash extends Migration {
    public function up() {
        AdsSchema::create('ads_hashes', function (BlueprintHelper $table) {
            $table->unsignedBigInteger('place_id');
            $table->unsignedBigInteger('campaign_id');
            $table->ipAddress('ip');
            $table->string("hash", 32);
            $table->boolean('click');
            $table->integer("expiry")->index();
            $table->unique(['place_id', 'campaign_id', 'ip', 'hash', 'click']);
            $table->engine = 'Memory';
        });
    }

    public function down() {
        AdsSchema::dropIfExists('ads_hashes');
    }
}
