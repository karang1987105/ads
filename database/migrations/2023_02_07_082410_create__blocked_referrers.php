<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedReferrers extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('blocked_referrers', function (Blueprint $table) {
            $table->engine = 'MEMORY';

            $table->id();
            $table->unsignedBigInteger("ip")->index();
            $table->unsignedBigInteger("place_id")->index();
            $table->timestamp('created_at');

            $table->unique(['ip', 'place_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('table_blocked_referrers');
    }
}
