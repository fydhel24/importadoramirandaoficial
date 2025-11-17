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
Schema::create('caja_sucursal', function (Blueprint $table) {
            $table->id();
            $table->string('total_vendido'); // Campo para el total vendido
            $table->string('qr'); // Campo para el QR
            $table->string('efectivo'); // Campo para el efectivo
            $table->string('qr_oficial')->nullable(); // Campo para el QR oficial
            $table->string('efectivo_oficial')->nullable();

            // Claves foráneas
            $table->unsignedBigInteger('sucursal_id'); // Campo para sucursal_id
            $table->unsignedBigInteger('fecha_sucursal_id'); // Campo para fecha_sucursal_id

            // Definir las claves foráneas
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('fecha_sucursal_id')->references('id')->on('fecha_sucursal')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_sucursal');
    }
};
