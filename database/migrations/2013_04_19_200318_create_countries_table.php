<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountriesTable extends Migration {
    public function up() {
        AdsSchema::create('countries', function (BlueprintHelper $table) {
            $table->char('id', 2, ascii: true);
            $table->string('name');
            $table->boolean('hidden')->default(false);
            $table->enum('category', ['Tier 1', 'Tier 2', 'Tier 3', 'Tier 4']);
            $table->integer("utc_start");
            $table->primary('id');
            $table->unique(['id', 'category']);
        });
    }

    public function down() {
        AdsSchema::dropIfExists('countries');
    }
}
