<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.order_history', 'order_history'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');

            $table->string('transition');
            $table->string('from');
            $table->string('to');
            $table->integer('actor_id')->nullable();

            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['actor_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.order_history', 'order_items'));
    }
}
