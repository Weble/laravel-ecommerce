<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Weble\LaravelEcommerce\Cart\CartItemModel;
use Weble\LaravelEcommerce\Customer\CustomerModel;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.orders', 'orders'), function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->foreignUuid('cart_id')->nullable();
            $table->char('customer_id', 40)->nullable();
            $table->foreignIdFor(config('ecommerce.classes.user', \App\Models\User::class))->nullable();
            $table->json('customer')->nullable();
            $table->char('currency', 3)->default(config('ecommerce.currency.default', 'USD'));
            $table->string('state')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->json('discounts');
            $table->bigInteger('discounts_subtotal')->default(0);
            $table->bigInteger('paid')->default(0);
            $table->bigInteger('items_subtotal')->default(0);
            $table->bigInteger('items_total')->default(0);
            $table->bigInteger('shipping_subtotal')->default(0);
            $table->bigInteger('shipping_total')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('tax')->default(0);
            $table->bigInteger('total')->default(0);
            $table->string('tracking_code')->nullable();
            $table->float('exchange_rate')->default(1);
            $table->string('invoice_number')->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.orders', 'orders'));
    }
}
