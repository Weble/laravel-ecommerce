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
        Schema::create(config('ecommerce.tables.discounts', 'cart_discounts'), function (Blueprint $table) {
            $table->char('id', 40)->primary(); // Sha1
            $table->char('session_id')->nullable()->index(); // Max length for a session_id
            $table->foreignIdFor(config('ecommerce.classes.user', '\\App\\Models\\User'))->nullable();
            $table->string('instance')->index();
            $table->string('type');
            $table->string('target');
            $table->bigInteger('value')->default(0);
            $table->char('currency', 3)->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.discounts', 'cart_discounts'));
    }
}
