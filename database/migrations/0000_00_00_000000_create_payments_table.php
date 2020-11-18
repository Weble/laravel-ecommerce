<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Weble\LaravelEcommerce\Order\Order;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.payments', 'payments'), function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class);
            $table->char('currency', 3)->default(config('ecommerce.currency.default', 'USD'));
            $table->string('state')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('transaction_id')->nullable();
            $table->bigInteger('total')->default(0);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.payments', 'payments'));
    }
}
