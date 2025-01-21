<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesIpsTable extends Migration {
    public function up() {
        AdsSchema::create('countries_ips', function (BlueprintHelper $table) {
            $table->unsignedBigInteger("start")->index();
            $table->unsignedBigInteger("end")->index();
            $table->char("country", 2)->index();
        });
        AdsSchema::create('countries_ips_temp', function (BlueprintHelper $table) {
            $table->unsignedBigInteger("start")->index();
            $table->unsignedBigInteger("end")->index();
            $table->char("country", 2)->index();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('countries_ips');
        AdsSchema::dropIfExists('countries_ips_temp');
    }
}
