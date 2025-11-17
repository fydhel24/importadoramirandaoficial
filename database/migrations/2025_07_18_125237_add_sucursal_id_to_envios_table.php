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
        Schema::table('envios', function (Blueprint $table) {
            // Asegurarse de que el campo permita valores nulos si lo deseas
            $table->unsignedBigInteger('sucursal_id')->nullable()->after('id_pedido');

            // Establecer la relación
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
