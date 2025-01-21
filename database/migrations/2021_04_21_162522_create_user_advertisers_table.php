<?php

use App\Helpers\AdsSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserAdvertisersTable extends Migration {
    public function up() {
        AdsSchema::create('users_advertisers', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('users_advertisers');
    }
}
