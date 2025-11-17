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
        Schema::create('inventariohistorial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sucursal');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_sucursal_origen');
            $table->unsignedBigInteger('id_user_destino');
            $table->dateTime('fecha_envio');
            $table->unsignedBigInteger('id_inventario');
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_sucursal')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_sucursal_origen')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('id_user_destino')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventariohistorial');
    }

};
