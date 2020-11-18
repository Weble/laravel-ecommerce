<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartitemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.items', 'cart_items'), function (Blueprint $table) {
            $table->char('id', 40)->primary();
            $table->string('cart_key');
            $table->bigInteger('user_id')->nullable();
            $table->string('instance')->index();
            $table->bigInteger('purchasable_id');
            $table->string('purchasable_type');
            $table->bigInteger('price');
            $table->float('quantity')->default(1);
            $table->json('product_attributes');
            $table->json('discounts');
            $table->timestamps();

            $table->index(['cart_key']);
            $table->index(['purchasable_type', 'purchasable_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.items', 'cart_items'));
    }
}
