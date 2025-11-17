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
 
        // Tabla intermedia para relacionar promociones con productos 
        Schema::create('promocion_producto', function (Blueprint $table) { 
            $table->id(); 
            $table->foreignId('id_promocion')->constrained('promociones')->onDelete('cascade'); 
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade'); 
            $table->integer('cantidad'); // Cantidad de productos 
            $table->decimal('precio_unitario', 10, 2); // Precio unitario editable 
            $table->timestamps(); 
        }); 
    } 
    /** 
     * Reverse the migrations. 
     */ 
    public function down(): void 
    { 
        Schema::dropIfExists('promocion_producto'); 
    } 
};
