<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTableTbMerchantGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_merchant_group', function (Blueprint $table) {
            $table->string('merchant_group_id')->unique();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('level')->nullable();
            $table->timestamps();
            $table->primary('merchant_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tb_merchant_group');
    }
}
