<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class AddTableSettings extends Migration {
    public function up() {
        AdsSchema::create('settings', function (BlueprintHelper $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('value');

            $table->timestamps();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('settings');
    }
}
