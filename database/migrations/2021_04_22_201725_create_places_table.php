<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreatePlacesTable extends Migration {
    public function up() {
        AdsSchema::create('places', function (BlueprintHelper $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('domain_id')->index();
            $table->unsignedBigInteger('ad_type_id')->index();
            $table->uuid('uuid')->index();
            $table->timestamps();
            $table->approvable();

            $table->foreign('domain_id')->references('id')->on('users_publishers_domains')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('ad_type_id')->references('id')->on('ads_types')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('places');
    }
}
