<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesCountriesTable extends Migration {
    public function up() {
        AdsSchema::create('categories_countries', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
            $table->char('country_id', 2, ascii: true)->index();
            $table->amount('cpm')->index()->nullable();
            $table->amount('cpc')->index()->nullable();
            $table->amount('cpv')->index()->nullable();

            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->restrictOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('categories_countries');
    }
}
