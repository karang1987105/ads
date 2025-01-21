<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignCountriesTable extends Migration {
    public function up() {
        AdsSchema::create('campaigns_countries', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->char('country_id', 2, ascii: true)->nullable()->comment('NULL for Tier 4');

            $table->unique(['campaign_id', 'country_id']);
            $table->foreign('campaign_id')->references('id')->on('campaigns')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('campaigns_countries');
    }
}
