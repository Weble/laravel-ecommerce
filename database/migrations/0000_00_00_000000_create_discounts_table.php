<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Weble\LaravelEcommerce\Order\Order;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.discounts', 'discounts'), function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('code')->nullable();
            $table->string('type');
            $table->string('target');
            $table->bigInteger('value')->default(0);
            $table->char('currency', 3)->default(config('ecommerce.currency.default', 'USD'));
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.discounts', 'discounts'));
    }
}
