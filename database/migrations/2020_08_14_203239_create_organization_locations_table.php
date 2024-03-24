<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->string('address')->nullable();
            $table->string('longitude');
            $table->string('latitude');
            $table->string('tel')->nullable();
            $table->string('fax')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->text('zip')->nullable();
            $table->string('opening')->nullable();
            $table->string('working_days')->nullable();
            $table->bigInteger('created_by')->nullable();
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
        Schema::dropIfExists('organization_locations');
    }
}
