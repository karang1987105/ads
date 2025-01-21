<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdTypesTable extends Migration {
    public function up() {
        AdsSchema::create('ads_types', function (BlueprintHelper $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Banner', 'Video']);
            $table->enum('kind', ['CPC', 'CPM', 'CPV']);
            $table->integer('width');
            $table->integer('height');
            $table->boolean('active');
            $table->timestamps();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('ads_types');
    }
}
