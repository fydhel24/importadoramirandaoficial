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
        Schema::table('cajas', function (Blueprint $table) {
            // Agregar el campo sucursal_id como clave foránea
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cajas', function (Blueprint $table) {
            // Eliminar la clave foránea y el campo
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
