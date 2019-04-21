<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessengerWatchItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messenger_watch_items', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('recipient_id')->unsigned();
            $table->string('product_id');
            $table->string('name');
            $table->string('image');
            $table->string('store_url');
            $table->string('api_url');
            $table->timestamp('fetched_at');
            $table->timestamps();

            //Indexes
            $table->unique(['recipient_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messenger_watch_item');
    }
}
