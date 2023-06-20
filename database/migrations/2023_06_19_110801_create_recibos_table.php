<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('balance_id')->nullable();
            $table->foreign('balance_id')->references('id')->on('balances_duca.balances')->constrained()
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->unsignedBigInteger('dictamen_id');
            $table->foreign('dictamen_id')->references('id')->on('dictamenes')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->float('recibo', 10,3);
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
        Schema::table('recibos', function (Blueprint $table) {
            $table->dropForeign(['balance_id']);
            $table->dropForeign(['dictamen_id']);
        });

        Schema::dropIfExists('recibos');
    }
};
