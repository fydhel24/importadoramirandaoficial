<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Eliminar la tabla 'envios' si existe
        Schema::dropIfExists('envios');

        // Crear la nueva tabla 'envios' con los campos actualizados
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->string('celular')->nullable();
            $table->string('departamento')->nullable();
            $table->string('monto_de_pago')->nullable(); // Cambiado a string
            $table->text('descripcion')->nullable();
            $table->boolean('lapaz')->nullable()->default(false);
            $table->boolean('enviado')->nullable()->default(false);
            $table->boolean('extra')->nullable()->default(false);
            $table->boolean('extra1')->nullable()->default(false);
            $table->datetime('fecha_hora_enviado')->nullable();
            $table->datetime('fecha_hora_creada')->nullable();
            $table->unsignedBigInteger('id_pedido')->nullable();
            $table->foreign('id_pedido')->references('id')->on('pedidos')->onDelete('set null');
            
            // Nuevos campos 'detalle' y 'estado'
            $table->string('detalle')->nullable();
            $table->string('estado')->nullable();

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
        // Eliminar la tabla 'envios' si se revierte la migración
        Schema::dropIfExists('envios');
    }
};
