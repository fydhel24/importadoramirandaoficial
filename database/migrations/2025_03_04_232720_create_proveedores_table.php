<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo_factura');
            $table->decimal('pago_inicial', 10, 2);
            $table->decimal('deuda_total', 10, 2);
            $table->date('fecha_registro');
            $table->enum('estado', ['Pagado', 'Saldo pendiente']);
            $table->string('foto_factura')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
