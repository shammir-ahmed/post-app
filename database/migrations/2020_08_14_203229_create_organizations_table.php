<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('card_number');
            $table->string('client_type');
            $table->string('official_name');
            $table->string('license_number');
            $table->string('emirate');
            $table->string('account_manager')->nullable();
            $table->string('single_use_credit');
            $table->string('license_expire_date');
            $table->text('office_address');
            $table->string('billing_referance')->nullable();
            $table->string('vat_number');
            $table->string('vat_status');
            $table->string('display_name');
            $table->string('website')->nullable();
            $table->string('default_email');
            $table->string('main_phone_number');
            $table->string('orn_number')->nullable();
            $table->string('rera_expire_date')->nullable();
            $table->string('license_doc')->nullable();
            $table->string('vat_doc')->nullable();
            $table->string('rera_doc')->nullable();
            $table->string('logo')->nullable();
            $table->float('rating', 3, 2)->default(0);
            $table->bigInteger('rating_count')->default(0);
            $table->text('description_en')->nullable();
            $table->text('key_description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('key_description_ar')->nullable();
            $table->json('slider')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}
