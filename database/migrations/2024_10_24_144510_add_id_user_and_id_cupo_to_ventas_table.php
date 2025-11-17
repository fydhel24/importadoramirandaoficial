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
            // Agregar nuevas columnas
            $table->foreignId('id_user')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('id_cupo')->nullable()->constrained('cupos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Eliminar columnas
            $table->dropForeign(['id_user']);
            $table->dropForeign(['id_cupo']);
            $table->dropColumn(['id_user', 'id_cupo']);
        });
    }
};
