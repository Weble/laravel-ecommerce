<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Weble\LaravelEcommerce\Order\Order;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.order_items', 'order_items'), function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class);

            $table->morphs('purchasable');
            $table->json('purchasable_data');
            $table->float('quantity')->default(1);
            $table->json('product_attributes');
            $table->json('discounts');

            $table->bigInteger('unit_price');
            $table->bigInteger('discounts_subtotal');
            $table->bigInteger('subtotal');

            $table->timestamps();
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
