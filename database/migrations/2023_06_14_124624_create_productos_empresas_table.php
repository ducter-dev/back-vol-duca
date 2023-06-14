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
        Schema::create('productos_empresas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('empresa_id')->constrained()
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
        Schema::table('productos_empresas', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['empresa_id']);
        });
        Schema::dropIfExists('productos_empresas');
    }
};
