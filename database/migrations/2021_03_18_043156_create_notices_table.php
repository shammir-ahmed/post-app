<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('notice_type')->index()->comment('notice, discuss, alert, warning');
            $table->string('title')->nullable();
            $table->text('notice');
            $table->timestamp('published_at')->index()->nullable();
            $table->boolean('dismissable')->default(false);
            $table->bigInteger('created_by')->index();
            $table->bigInteger('updated_by')->index();
            $table->softDeletes()->index();
            $table->timestamps();
        });

        Schema::create('noticeables', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('notice_id')->index();
            $table->bigInteger('user_id')->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('closed_at')->nullable()->index();

            $table->unique(['notice_id', 'user_id']);
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notices');
        Schema::dropIfExists('noticeable');
    }
}
