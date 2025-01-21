<?php

use App\Helpers\AdsSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCategoriesAddFloatingPoints extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('categories', function (Blueprint $table) {
            $table->decimal('cpm', 12, 6, true)->change();
            $table->decimal('cpc', 12, 6, true)->change();
            $table->decimal('cpv', 12, 6, true)->change();
        });

        Schema::table('categories_countries', function (Blueprint $table) {
            $table->decimal('cpm', 12, 6, true)->nullable()->change();
            $table->decimal('cpc', 12, 6, true)->nullable()->change();
            $table->decimal('cpv', 12, 6, true)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        AdsSchema::table('categories', function (\App\Helpers\BlueprintHelper $table) {
            $table->amount('cpm');
            $table->amount('cpc');
            $table->amount('cpv');
        });

        AdsSchema::table('categories_countries', function (Blueprint $table) {
            $table->amount('cpm')->index()->nullable();
            $table->amount('cpc')->index()->nullable();
            $table->amount('cpv')->index()->nullable();
        });
    }
}
