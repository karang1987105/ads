<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicePlacesTable extends Migration {
    public function up() {
        AdsSchema::create('invoices_places', function (BlueprintHelper $table) {
            $table->unsignedBigInteger('invoice_id')->primary();
            $table->unsignedBigInteger('campaign_id')->index();
            $table->unsignedBigInteger('place_id')->index();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnUpdate()->cascadeOnDelete();

            // FKs removed to make it possible to delete campaigns and places when there is invoice for that.
            //$table->foreign('campaign_id')->references('id')->on('campaigns')->cascadeOnUpdate()->cascadeOnDelete();
            //$table->foreign('place_id')->references('id')->on('places')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('invoices_places');
    }
}
