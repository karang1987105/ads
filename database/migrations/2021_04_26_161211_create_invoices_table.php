<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration {
    public function up() {
        AdsSchema::create('invoices', function (BlueprintHelper $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('user_id')->index();
            $table->amount('amount', false);
            $table->unsignedBigInteger('payment_id')->nullable()->index()->comment('Reference to payment if paid');
            $table->boolean('archived')->default(false);
            $table->boolean('bonus')->default(false);
            $table->unsignedBigInteger('withdrawal_request_id')->nullable()->index()->comment('Available for publishers invoices');
            $table->timestamps();
            $table->foreign('withdrawal_request_id')->on('withdrawals_requests')->references('id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('user_id')->on('users')->references('id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('payment_id')->on('payments')->references('id')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('invoices');
    }
}
