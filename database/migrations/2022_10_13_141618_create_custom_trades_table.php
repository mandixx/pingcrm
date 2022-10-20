<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate()->index('user-id-custom-trades-idx');
            $table->foreignId('api_pair_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate()->index('pair-id-custom-trades-idx');
            $table->text('symbol')->index('symbol-custom-trades-idx')->nullable()->default(null);
            $table->longText('buy_order_id')->nullable()->default(null);
            $table->longText('sell_order_id')->nullable()->default(null);
            $table->longText('order_list_id')->nullable()->default(null);
            $table->double('price_buy')->nullable()->default(0);
            $table->double('price_sell')->nullable()->default(0);
            $table->double('qty')->nullable()->default(0);
            $table->double('commission')->nullable()->default(0);
            $table->double('profit')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_trades');
    }
}
