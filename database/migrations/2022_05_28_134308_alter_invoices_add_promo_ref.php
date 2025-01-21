<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesAddPromoRef extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('promo')->nullable()->after('archived');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('promo');
        });
    }
}
