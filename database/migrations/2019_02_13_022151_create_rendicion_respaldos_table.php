<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRendicionRespaldosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('rendicion_respaldos', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('rendicion_id');
        $table->dateTime('fecha_respaldo');
        $table->string('tipo_respaldo', 20);
        $table->string('nit', 20);
        $table->string('nro_respaldo', 20);
        $table->string('codigo_autorizacion', 30);
        $table->string('codigo_control', 20);
        $table->string('razon_social');
        $table->string('detalle', 500);
        $table->string('corresponde_a');
        $table->decimal('monto',8,2);
        $table->string('valido');
        $table->string('observaciones');
        $table->string('estado');
        $table->integer('usuario_creacion');
        $table->integer('usuario_modificacion');
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
      Schema::drop('rendicion_respaldos');
    }
}
