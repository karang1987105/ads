<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserManagersTable extends Migration {
    public function up() {
        AdsSchema::create('users_managers', function (BlueprintHelper $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->boolean('is_admin')->nullable()->unique();
            $table->set('publishers', ['List', 'Create', 'Update', 'Delete', 'Block', 'Activate', 'Send Email', 'Add Fund', 'Remove Fund', 'Login Behalf'])->nullable();
            $table->set('advertisers', ['List', 'Create', 'Update', 'Delete', 'Block', 'Activate', 'Send Email', 'Add Fund', 'Remove Fund', 'Login Behalf'])->nullable();
            $table->set('advertisements', ['Create', 'Update', 'Delete', 'Block', 'Activate'])->nullable();
            $table->set('promos', ['Create', 'Update', 'Delete'])->nullable();
            $table->boolean('send_mass_email')->default(false);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('users_managers');
    }
}
