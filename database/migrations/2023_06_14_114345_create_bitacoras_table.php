<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Evento;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->dateTime('fecha_hora');
            $table->foreignId('evento_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('descripcion1');
            $table->string('descripcion2');
            $table->string('descripcion3');
            $table->foreignId('usuario_id')->constrained()
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
        Schema::table('bitacoras', function (Blueprint $table) {
            $table->dropForeign(['evento_id']);
            $table->dropForeign(['balance_id']);
        });
        Schema::dropIfExists('bitacoras');
    }
};
