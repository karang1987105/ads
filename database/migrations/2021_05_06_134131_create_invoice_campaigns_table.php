<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceCampaignsTable extends Migration {
    public function up() {
        AdsSchema::create('invoices_campaigns', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->index();
            $table->unsignedBigInteger('campaign_id')->index();
            $table->amount('amount', unsigned: false);
            $table->amount('current', unsigned: false)->default(0);

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('invoices_campaigns');
    }
}
