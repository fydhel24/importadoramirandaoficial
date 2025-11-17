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
    Schema::table('cupos', function (Blueprint $table) {
        // Añadir la columna de relación con 'users'
        $table->foreignId('id_user')->nullable()->constrained('users')->onDelete('set null');
        
        // Cambiar las columnas a 'dateTime' para guardar fecha y hora
        $table->dateTime('fecha_inicio')->nullable();  // 'dateTime' en lugar de 'date'
        $table->dateTime('fecha_fin')->nullable();     // 'dateTime' en lugar de 'date'
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::table('cupos', function (Blueprint $table) {
        // Eliminar las restricciones de la clave foránea
        $table->dropForeign('cupos_id_user_foreign');
        
        // Eliminar las columnas
        $table->dropColumn('id_user');
        $table->dropColumn('fecha_inicio');
        $table->dropColumn('fecha_fin');
    });
}


};
