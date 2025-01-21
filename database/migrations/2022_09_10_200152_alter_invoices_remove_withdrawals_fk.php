<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesRemoveWithdrawalsFk extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_withdrawal_request_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('withdrawal_request_id')->on('withdrawals_requests')->references('id')->cascadeOnUpdate()->nullOnDelete();
        });
    }
}
