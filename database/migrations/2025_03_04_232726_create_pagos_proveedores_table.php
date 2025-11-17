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
        Schema::create('pagos_proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->decimal('monto_pago', 10, 2);
            $table->date('fecha_pago');
            $table->decimal('saldo_restante', 10, 2);
            $table->string('foto_factura')->nullable(); // Permitir valores nulos para la imagen de la factura
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_proveedores');
    }

};
