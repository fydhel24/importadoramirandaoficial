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
         Schema::create('pedido_producto', function (Blueprint $table) {
            $table->id();  // ID único para la tabla intermedia
            $table->foreignId('id_pedido')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad');  // Cantidad de productos en el pedido
            $table->decimal('precio', 10, 2);  // Precio del producto en el momento del pedido
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');  // Usuario que realiza el pedido
            $table->date('fecha');  // Fecha del pedido
            $table->timestamps();  // Timestamps (created_at, updated_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_producto');
    }
};
