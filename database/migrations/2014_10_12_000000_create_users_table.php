<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        AdsSchema::create('users', function (BlueprintHelper $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->enum('type', ['Manager', 'Advertiser', 'Publisher'])->nullable(false); // Admin is Manager
            $table->timestamp('active')->nullable(); // NULL: Inactive, Future: Blocked until..., Past: Active

            $table->char('country_id', 2, ascii: true)->nullable()->index();
            $table->string('company');
            $table->string('phone');
            $table->string('business_id');
            $table->string('address');
            $table->string('state');
            $table->string('city');
            $table->string('zip');

            $table->foreign('country_id')->references('id')->on('countries')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        AdsSchema::dropIfExists('users');
    }
}
