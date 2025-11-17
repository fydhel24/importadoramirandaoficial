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
        Schema::table('inventario', function (Blueprint $table) {
            // Agregar nueva columna
            $table->foreignId('id_user')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario', function (Blueprint $table) {
            // Eliminar columna
            $table->dropForeign(['id_user']);
            $table->dropColumn('id_user');
        });
    }
};
