<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('deviceId')->unsigned();
            $table->integer('appId')->unsigned();
            $table->integer('receipt')->unsigned();
            $table->enum('status', ['True','False'])->nullable();
            $table->dateTime('expire_date')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->foreign('deviceId')->references('id')->on('devices');
            $table->unique(['deviceId','appId']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
