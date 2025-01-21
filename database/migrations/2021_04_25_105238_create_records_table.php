<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateRecordsTable extends Migration {
    public function up() {
        AdsSchema::create('records', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->unsignedBigInteger('place_id')->nullable()->index();
            $table->char('country_id', 2, ascii: true)->index();
            $table->amount('cost')->index();
            $table->percent('revenue')->index();
            $table->dateTime('time')->index();

            $table->foreign('campaign_id')->on('campaigns')->references('id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('place_id')->on('places')->references('id')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('records');
    }
}
