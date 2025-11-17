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
        Schema::table('ventas', function (Blueprint $table) {
            // Eliminar columnas antiguas
            $table->dropForeign(['id_producto']); // Si existe
            $table->dropForeign(['id_pedido']); // Si existe
            $table->dropColumn(['id_producto', 'id_pedido', 'costo_compra']); // Eliminar las columnas antiguas

            // Agregar nuevas columnas
            $table->string('nombre_cliente'); // Nombre del cliente
            $table->decimal('costo_total', 10, 2); // Costo total de la venta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Revertir cambios
            $table->foreignId('id_producto')->constrained('productos');
            $table->foreignId('id_pedido')->constrained('pedidos');
            $table->decimal('costo_compra', 10, 2);
            $table->dropColumn(['nombre_cliente', 'costo_total']);
        });
    }
};
