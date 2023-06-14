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
        Schema::create('densidades', function (Blueprint $table) {
            $table->id();
            $table->float('densidad', 10,4);
            $table->unsignedBigInteger('balance_id')->nullable();
            $table->foreign('balance_id')->references('id')->on('balances_duca.balances')->constrained()
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
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
        Schema::table('densidades', function (Blueprint $table) {
            $table->dropForeign(['balance_id']);
        });
        Schema::dropIfExists('densidades');
    }
};
