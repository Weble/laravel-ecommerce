<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartcustomersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.customers', 'cart_customers'), function (Blueprint $table) {
            $table->char('id', 40)->primary();
            $table->bigInteger('user_id')->nullable();
            $table->json('billing_address');
            $table->json('shipping_address');
            $table->timestamps();

            $table->index(['user_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.customers', 'cart_customers'));
    }
}
