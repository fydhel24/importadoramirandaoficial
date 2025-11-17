<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;
return new class extends Migration
{
        /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ci');
            $table->string('celular');
            $table->string('destino');
            $table->string('direccion');
            $table->string('estado');
           $table->integer('cantidad_productos'); // Campo para cantidad de productos
            $table->text('detalle'); // Campo para detalles adicionales
            $table->text('productos'); // Campo para información sobre productos (puede ser un JSON o texto largo)
            $table->decimal('monto_deposito', 10, 2); // Campo para monto de depósito
            $table->decimal('monto_enviado_pagado', 10, 2); // Campo para monto enviado o pagado
            $table->date('fecha'); // Campo para fecha
            $table->foreignId('id_semana')->constrained('semanas')->onDelete('cascade');
            $table->string('foto_comprobante')->nullable(); // Agrega la columna foto_comprobante
            $table->string('codigo')->nullable(); // Agrega la columna codigo
            $table->timestamps();
        });
          // Cambiar el valor inicial del autoincremento
    DB::statement('ALTER TABLE pedidos AUTO_INCREMENT = 1000;');
  
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};
