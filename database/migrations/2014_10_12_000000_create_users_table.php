<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('client_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username', 100)->unique()->nullable(); // optional
            $table->string('email', 100)->unique();
            $table->string('password', 100)->nullable()->comment('minimum 6 character');
            $table->string('phone', 14)->nullable();
            $table->string('avatar')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('zip')->nullable();
            $table->string('timezone')->nullable();
            $table->enum('gender', ['male', 'female', 'none'])->default('male');
            $table->enum('online', ['online', 'offline', 'away'])->default('offline');
            $table->enum('status', ['unverified', 'pending', 'active', 'suspend', 'cencel'])->default('unverified');
            $table->string('user_type')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE users ADD FULLTEXT users_fulltext_index (first_name,last_name,email,phone)");
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropIndex('users_fulltext_index');
        });
        Schema::dropIfExists('users');
    }
}
