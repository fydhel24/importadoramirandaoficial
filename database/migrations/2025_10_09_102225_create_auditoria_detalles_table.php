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
        Schema::create('auditoria_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditoria_id')->constrained('auditorias_inventario')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->integer('stock_sistema');
            $table->integer('stock_real');
            $table->integer('diferencia');
            $table->string('estado')->nullable(); 
            $table->text('comentario')->nullable(); 
            $table->string('observacion_solucion')->nullable();
            $table->date('fecha_solucion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_detalles');
    }
};
