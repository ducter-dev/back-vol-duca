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
        Schema::create('dictamenes', function (Blueprint $table) {
            $table->id();
            $table->string('rfcDictamen');
            $table->string('loteDictamen');
            $table->string('folioDictamen');
            $table->date('fechaInicioDictamen');
            $table->date('fechaEmisionDictamen');
            $table->string('resultadoDictamen');
            $table->float('densidad', 10,4);
            $table->float('volumen', 10,2);
            $table->foreignId('cliente_id')->nullable()->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('rutaDictamen')->nullable();
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
        Schema::table('dictamenes', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['balance_id']);
        });
        Schema::dropIfExists('dictamenes');
    }
};
