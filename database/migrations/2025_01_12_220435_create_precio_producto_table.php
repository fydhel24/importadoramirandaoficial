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
       Schema::create('precio_producto', function (Blueprint $table) {
            $table->id(); // Campo ID auto incremental
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade'); // Relación con el modelo Producto
            $table->decimal('precio_jefa', 10, 2)->nullable(); // Campo para el precio jefa
            $table->decimal('precio_unitario', 10, 2)->nullable();  // Campo para el precio unitario
            $table->decimal('cantidad', 10, 2)->nullable();  // Campo para el precio unitario
            $table->decimal('precio_general', 10, 2)->nullable();  // Campo para el precio general
            $table->decimal('precio_extra', 10, 2)->nullable();  // Campo para el precio extra
            $table->dateTime('fecha_creada')->nullable();  // Cambiado a datetime sin valor predeterminado
            $table->timestamps(); // Timestamps de Laravel (created_at, updated_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precio_producto');
    }
};
