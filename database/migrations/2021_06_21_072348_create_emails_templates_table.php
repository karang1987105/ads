<?php

use App\Helpers\AdsSchema;
use App\Helpers\BlueprintHelper;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsTemplatesTable extends Migration {
    public function up() {
        AdsSchema::create('emails_templates', function (BlueprintHelper $table) {
            $table->id();
            $table->string("title");
            $table->string("subject");
            $table->text("message");
        });
        AdsSchema::create('emails_templates_attachments', function (BlueprintHelper $table) {
            $table->id();
            $table->unsignedBigInteger('email_template_id')->index();
            $table->string('name');
            $table->string('attachment');
            $table->boolean('inline');
            $table->foreign('email_template_id')->references('id')->on('emails_templates')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        AdsSchema::dropIfExists('emails_templates');
        AdsSchema::dropIfExists('emails_templates_attachments');
    }
}
