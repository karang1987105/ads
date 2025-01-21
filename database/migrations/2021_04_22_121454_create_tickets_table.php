<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration {
    public function up() {
        AdsSchema::create('tickets_threads', function (BlueprintHelper $table) {
            $table->id();
            $table->string('subject');
            $table->enum('category', ['Publishers', 'Advertisers', 'Billing', 'Other'])->default('Other');
            $table->boolean('closed')->default(false);
            $table->timestamps();
        });

        AdsSchema::create('tickets_messages', function (BlueprintHelper $table) {
            $table->id();
            $table->boolean('reply');
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('NULL for guest tickets');
            $table->string('guest')->nullable()->comment('Guest\'s email address to reply');
            $table->text('message');
            $table->timestamps();
            $table->foreign('thread_id')->references('id')->on('tickets_threads')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('tickets_threads');
        AdsSchema::dropIfExists('tickets_messages');
    }
}
