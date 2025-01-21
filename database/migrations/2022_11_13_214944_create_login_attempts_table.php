<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLoginAttemptsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        AdsSchema::create('login_attempts', function (BlueprintHelper $table) {
            $table->id();
            $table->string('email');
            $table->string('ip');
            $table->char('country', 2, ascii: true)->nullable();
            $table->boolean('successful');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('login_attempts');
    }
}
