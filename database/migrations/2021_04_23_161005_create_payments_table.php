<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration {
    public function up() {
        AdsSchema::create('payments', function (BlueprintHelper $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('user_id')->comment('The user who is selling/purchasing services.');
            $table->char('currency_id', 4, ascii: true)->nullable()->index()->comment("NULL when currency is deleted.");
            $table->amount('amount', false)->index()->comment('USD. Positive means deposit, Negative means withdrawal.');
            $table->amount('exchange_rate', crypto: true)->index()->nullable()
                ->comment('Exchange rate of the amount to currency. It\'s NULL and will be set on confirming the payment');
            $table->timestamps();
            $table->approvable('confirmed');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            // Make it possible to delete currencies
            //$table->foreign('currency_id')->references('id')->on('currencies')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('payments');
    }
}
