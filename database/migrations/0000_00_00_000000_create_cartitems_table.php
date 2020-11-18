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
            $table->uuid('id')->primary();
            $table->string('cart_key');
            $table->foreignIdFor(config('ecommerce.classes.user', \App\Models\User::class))->nullable();
            $table->string('instance')->index();
            $table->morphs('purchasable');
            $table->bigInteger('price');
            $table->float('quantity')->default(1);
            $table->json('product_attributes');
            $table->json('discounts');
            $table->timestamps();

            $table->index(['cart_key']);
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
