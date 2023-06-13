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
        Schema::connection('mysql2')->create('salidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balance_id')->nullable()->constrained()
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin');
            $table->float('valor', 10,3);
            $table->string('tipo', 1);
            $table->string('llenadera', 10);
            $table->string('pg', 10);
            $table->integer('cliente');
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
        Schema::table('salidas', function (Blueprint $table) {
            $table->dropForeign(['balance_id']);
        });
        Schema::dropIfExists('salidas');
    }
};
