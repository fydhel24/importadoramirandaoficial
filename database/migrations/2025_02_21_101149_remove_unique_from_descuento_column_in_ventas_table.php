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
        Schema::table('ventas', function (Blueprint $table) {
            // Elimina la restricción UNIQUE en el campo descuento
            $table->dropUnique(['codigo_venta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Si necesitas revertir la migración, puedes volver a agregar la restricción UNIQUE
            $table->unique('codigo_venta');
        });
    }
};
