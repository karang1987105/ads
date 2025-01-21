<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateUserDomainsTable extends Migration {
    public function up() {
        AdsSchema::create('users_advertisers_domains', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index();
            $table->string('domain');
            $table->timestamps();
            $table->approvable();

            $table->foreign('advertiser_id')->on('users_advertisers')->references('user_id')->cascadeOnUpdate()->cascadeOnDelete();
        });
        AdsSchema::create('users_publishers_domains', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('publisher_id')->index();
            $table->string('domain');
            $table->unsignedBigInteger('category_id')->nullable()->comment('Set by managers.');
            $table->timestamps();
            $table->approvable();

            $table->foreign('publisher_id')->on('users_publishers')->references('user_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('category_id')->on('categories')->references('id')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('users_advertisers_domains');
        AdsSchema::dropIfExists('users_publishers_domains');
    }
}
