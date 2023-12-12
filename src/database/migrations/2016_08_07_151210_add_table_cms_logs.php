<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTableCmsLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('ipaddress', 50)->nullable();
            $table->longText('useragent')->nullable();
            $table->string('url')->nullable();
            $table->string('description')->nullable();
            $table->bigInteger('id_cms_users')->nullable();

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
        Schema::drop('cms_logs');
    }
}
