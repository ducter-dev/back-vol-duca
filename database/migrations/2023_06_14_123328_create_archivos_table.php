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
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ruta');
            $table->string('tipo');
            $table->foreignId('usuario_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedBigInteger('balance_id')->nullable();
            $table->foreign('balance_id')->references('id')->on('balances_duca.balances')->constrained()
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->integer('estado');
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
        Schema::table('archivos', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropForeign(['balance_id']);
        });
        Schema::dropIfExists('archivos');
    }
};
