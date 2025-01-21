<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateAdsTable extends Migration {
    public function up() {
        AdsSchema::create('ads', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('ad_type_id')->index();
            $table->unsignedBigInteger('advertiser_id')->nullable()->index()->comment('Null for admin');
            $table->boolean('is_third_party')->default(false);
            $table->timestamps();
            $table->approvable();

            $table->foreign('ad_type_id')->references('id')->on('ads_types')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('advertiser_id')->references('user_id')->on('users_advertisers')->cascadeOnUpdate()->cascadeOnDelete();
        });

        foreach (['Banner', 'Video', 'Third Party'] as $type) {
            $tableName = 'ads_' . Str::plural(str_replace(' ', '', strtolower($type)));

            AdsSchema::create($tableName, function (BlueprintHelper $table) use ($type) {
                $table->unsignedBigInteger('ad_id')->primary();
                $table->string('title', 30);

                if ($type === 'Banner' || $type === 'Video') {
                    $table->string('file');
                    $table->string('alt_text', 30);
                    $table->foreignId('domain_id')->references('id')->on('users_advertisers_domains')->cascadeOnUpdate()->cascadeOnDelete();
                    $table->string('url');

//                    if ($type === 'Video') {
//                        $table->boolean('loop');
//                    }
                } else {
                    $table->text('code');
                }

                $table->foreign('ad_id')->on('ads')->references('id')->cascadeOnUpdate()->cascadeOnDelete();
                return $table;
            });
        }
    }

    public function down() {
        AdsSchema::dropIfExists('ads');
        AdsSchema::dropIfExists('ads_banners');
        AdsSchema::dropIfExists('ads_videos');
        AdsSchema::dropIfExists('ads_thirdparties');
    }
}
