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
            $table->id();
            $table->char('cart_item_id', 40)->index(); // Sha1
            $table->char('session_id')->nullable()->index(); // Max length for a session_id
            $table->foreignIdFor(config('ecommerce.classes.user', '\\App\\Models\\User'))->nullable();
            $table->string('instance')->index();
            $table->morphs('purchasable');
            $table->bigInteger('price');
            $table->float('quantity')->default(1);
            $table->json('product_attributes');
            $table->json('discounts');
            $table->timestamps();

            $table->index(['cart_item_id', 'instance', 'session_id']);
            $table->index(['instance', 'user_id']);
            $table->index(['instance', 'session_id']);
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
