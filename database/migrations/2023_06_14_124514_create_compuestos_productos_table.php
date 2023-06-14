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
        Schema::create('compuestos_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('compuesto_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->float('porcentaje', 10,2);
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
        Schema::table('compuestos_productos', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['compuesto_id']);
        });
        Schema::dropIfExists('compuestos_productos');
    }
};
