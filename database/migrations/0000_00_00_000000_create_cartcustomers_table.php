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
            $table->char('session_id')->nullable()->index(); // Max length for a session_id
            $table->foreignIdFor(config('ecommerce.classes.user', '\\App\\Models\\User'))->nullable();
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
