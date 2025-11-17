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
        Schema::table('pedido_producto', function (Blueprint $table) {
            // Agregar la columna id_envio
            $table->unsignedBigInteger('id_envio')->nullable()->after('id_usuario');
            
            // Establecer la relación con la tabla envios
            $table->foreign('id_envio')->references('id')->on('envios')->onDelete('set null');

            // Hacer id_pedido nullable
            $table->unsignedBigInteger('id_pedido')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedido_producto', function (Blueprint $table) {
            // Eliminar la llave foránea
            $table->dropForeign(['id_envio']);
            
            // Eliminar la columna id_envio
            $table->dropColumn('id_envio');

            // Restaurar la no-nulidad de id_pedido
            $table->unsignedBigInteger('id_pedido')->nullable(false)->change();
        });
    }
};
