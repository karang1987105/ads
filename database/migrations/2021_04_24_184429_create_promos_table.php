<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePromosTable extends Migration {
    public function up() {
        AdsSchema::create('promos', function (BlueprintHelper $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('code', 100);
            $table->percent('bonus');
            $table->integer('total')->nullable()->comment('Null means unlimited');
            $table->integer('purchased')->default(0)->comment('Increments on using promo');
            $table->timestamps();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('promos');
    }
}
