<?php

use App\Helpers\AdsSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWithdrawalsRequestsTable extends Migration {
    public function up() {
        AdsSchema::create('withdrawals_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->char('currency', 5);
            $table->string('wallet', 255);
            $table->timestamps();

            $table->foreign('user_id')->on('users')->references('id')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('withdrawals_requests');
    }
}