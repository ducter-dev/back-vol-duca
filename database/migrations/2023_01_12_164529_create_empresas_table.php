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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->float('version', 10,2);
            $table->string('rfc_contribuyente');
            $table->string('rfc_representante');
            $table->string('proveedor');
            $table->string('tipo_caracter');
            $table->string('modalidad_permiso');
            $table->string('num_permiso');
            $table->string('clave_instalacion');
            $table->string('descripcion_instalacion');
            $table->string('geolocalizacion_latitud');
            $table->string('geolocalizacion_longitud');
            $table->integer('numero_tanques');
            $table->integer('numero_ductos_entradas_salidas');
            $table->integer('numero_ductos_distribucion');
            $table->dateTime('fecha_hora_corte');
            $table->integer('producto_omision');
            $table->timestamp('creado');
            $table->timestamp('actualizado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empresas');
    }
};
