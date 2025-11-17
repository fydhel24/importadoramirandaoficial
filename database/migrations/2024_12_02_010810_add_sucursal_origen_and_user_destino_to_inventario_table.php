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
        Schema::table('inventario', function (Blueprint $table) {
            // Agregar los campos de sucursal de origen, usuario de destino y fecha de transferencia
            $table->unsignedBigInteger('id_sucursal_origen')->nullable()->after('id_user');
            $table->unsignedBigInteger('id_user_destino')->nullable()->after('id_sucursal_origen');
            $table->dateTime('transfer_date')->nullable()->after('id_user_destino'); // Nuevo campo datetime

            // Relaciones con las tablas sucursales y usuarios
            $table->foreign('id_sucursal_origen')->references('id')->on('sucursales')->onDelete('set null');
            $table->foreign('id_user_destino')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->dropForeign(['id_sucursal_origen']);
            $table->dropForeign(['id_user_destino']);
            $table->dropColumn(['id_sucursal_origen', 'id_user_destino', 'transfer_date']);
        });
    }
};
