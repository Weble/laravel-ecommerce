<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEcommerceStateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('ecommerce.tables.state_history', 'ecommerce_state_history'), function (Blueprint $table) {
            $table->id();
            $table->morphs('model');

            $table->string('transition');
            $table->string('from');
            $table->string('to');
            $table->integer('actor_id')->nullable();

            $table->timestamps();

            $table->index(['actor_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('ecommerce.tables.state_history', 'ecommerce_state_history'));
    }
}
