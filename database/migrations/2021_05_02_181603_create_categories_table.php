<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration {
    public function up() {
        AdsSchema::create('categories', function (BlueprintHelper $table) {
            $table->id();
            $table->string('title');
            $table->amount('cpm')->index();
            $table->amount('cpc')->index();
            $table->amount('cpv')->index();
            $table->percent('revenue_share');
            $table->boolean('active');
            $table->timestamps();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('categories');
    }
}
