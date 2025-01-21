<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignsTable extends Migration {
    public function up() {
        AdsSchema::create('campaigns', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->index();
            $table->enum('device', ['Mobile', 'Desktop', 'All']);
            $table->decimal('revenue_ratio', 5, 4, true)->default(1);
            $table->boolean('enabled')->comment('For Advertiser to stop/start the campaign.');
            $table->boolean('proxy')->default(true)->comment('Disable to exclude vpn/proxy visits');
            $table->unsignedBigInteger('category_id');
            $table->uuid('uuid')->index();
            $table->integer('impressions')->default(0);
            $table->timestamps();

            $table->dateTime('stopped_at')->nullable()->comment('For manager/system to stop the campaign.');
            $table->unsignedBigInteger('stopped_by_id')->nullable()->index()->comment('If stopped_at has value nut stopped_by is NULL. Stopped by System can\'t be started');

            $table->foreign('category_id')->on('categories')->references('id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('stopped_by_id')->on('users_managers')->references('user_id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('ad_id')->on('ads')->references('id')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('campaigns');
    }
}
