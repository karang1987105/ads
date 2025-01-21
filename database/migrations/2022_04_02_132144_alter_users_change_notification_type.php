<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersChangeNotificationType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Because DBAL doesn't change column to set
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notifications');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->set('notifications', ['Account', 'Domain', 'Place', 'Withdrawal', 'Advertisement', 'Campaign'])->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notifications');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notifications')->default(true);
        });
    }
}
