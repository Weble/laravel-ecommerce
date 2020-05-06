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
        Schema::create(config('ecommerce.cart_table', 'cart_items'), function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->bigInteger('user_id')->nullable();
            $table->string('instance')->index();
            $table->bigInteger('purchasable_id');
            $table->string('purchasable_type');
            $table->bigInteger('price');
            $table->json('attributes');
            $table->timestamps();

            $table->index(['purchasable_type', 'purchasable_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.cart_table', 'cart_items'));
    }
}
