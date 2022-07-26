<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTableTbUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_users', function (Blueprint $table) {
            $table->string('user_id')->unique();
            $table->string('merchant_group_id');
            $table->string('merchant_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone_number')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->nullable()->default(0);
            $table->integer('login_attempt')->nullable()->default(0);
            $table->timestamp('last_login_datetime')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->string('role')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
            $table->primary('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tb_users');
    }
}
