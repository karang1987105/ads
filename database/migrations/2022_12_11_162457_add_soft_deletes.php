<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletes extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('ads', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('campaigns', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('invoices_campaigns', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('invoices_campaigns', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
