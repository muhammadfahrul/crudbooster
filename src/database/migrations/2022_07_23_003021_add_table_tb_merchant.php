<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTableTbMerchant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_merchant', function (Blueprint $table) {
            $table->string('merchant_id')->unique();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->longText('address')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->primary('merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tb_merchant');
    }
}
