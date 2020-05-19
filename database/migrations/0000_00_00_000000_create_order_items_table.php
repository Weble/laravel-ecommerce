<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.order_items', 'order_items'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');

            $table->bigInteger('purchasable_id');
            $table->string('purchasable_type');
            $table->json('purchasable_data');
            $table->float('quantity')->default(1);
            $table->json('product_attributes');
            $table->json('discounts');

            $table->bigInteger('unit_price');
            $table->bigInteger('discounts_subtotal');
            $table->bigInteger('subtotal');

            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['purchasable_type', 'purchasable_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.order_items', 'order_items'));
    }
}
