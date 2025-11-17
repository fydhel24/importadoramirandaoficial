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
            // Agregar las columnas 'efectivo' y 'qr_extra'
            $table->decimal('efectivo', 10, 2)->nullable(); // Nuevo campo para el efectivo
            $table->decimal('qr', 10, 2)->nullable(); // Nuevo campo para el QR
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Eliminar las columnas 'efectivo' y 'qr_extra'
            $table->dropColumn('efectivo');
            $table->dropColumn('qr');
        });
    }

};
