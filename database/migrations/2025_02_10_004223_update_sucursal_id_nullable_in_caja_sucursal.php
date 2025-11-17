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
        // Modificamos la tabla 'caja_sucursal'
        Schema::table('caja_sucursal', function (Blueprint $table) {
            // Cambiar la columna 'sucursal_id' para que sea nullable
            $table->unsignedBigInteger('sucursal_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertimos el cambio y hacemos la columna 'sucursal_id' no nullable
        Schema::table('caja_sucursal', function (Blueprint $table) {
            $table->unsignedBigInteger('sucursal_id')->nullable(false)->change();
        });
    }

};
