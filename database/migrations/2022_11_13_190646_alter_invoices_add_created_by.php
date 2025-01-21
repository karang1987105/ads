<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesAddCreatedBy extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('invoices', function (Blueprint $table) {
            $table->after('withdrawal_request_id', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by_id')->nullable()->index();
                $table->foreign('created_by_id')->on('users_managers')->references('user_id')->cascadeOnUpdate()->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('created_by_id');
        });
    }
}
