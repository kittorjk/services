<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRendicionViaticosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('rendicion_viaticos', function (Blueprint $table) {
        $table->increments('id');
        $table->string('codigo', 20);
        $table->integer('stipend_request_id');
        $table->integer('nro_rendicion');
        $table->dateTime('fecha_deposito');
        $table->decimal('monto_deposito',8,2);
        $table->decimal('total_facturas_validas',8,2);
        $table->decimal('total_recibos_validos',8,2);
        $table->decimal('total_rendicion',8,2);
        $table->decimal('subtotal_alimentacion',8,2);
        $table->decimal('subtotal_transporte',8,2);
        $table->decimal('subtotal_combustible',8,2);
        $table->decimal('subtotal_taxi',8,2);
        $table->decimal('subtotal_comunicaciones',8,2);
        $table->decimal('subtotal_hotel',8,2);
        $table->decimal('subtotal_materiales',8,2);
        $table->decimal('subtotal_extras',8,2);
        $table->decimal('monto_sobrante',8,2);
        $table->decimal('saldo_favor_empresa',8,2);
        $table->boolean('devuelto_empresa');
        $table->decimal('monto_excedente',8,2);
        $table->decimal('saldo_favor_persona',8,2);
        $table->boolean('devuelto_persona');
        $table->string('observaciones', 500);
        $table->dateTime('fecha_presentado');
        $table->dateTime('fecha_estado');
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
      Schema::drop('rendicion_viaticos');
    }
}
