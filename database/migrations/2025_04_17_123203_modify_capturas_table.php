<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capturas', function (Blueprint $table) {
            $table->dropColumn('foto_modificada'); // Elimina la columna
            $table->unsignedBigInteger('carpeta_id')->nullable(); // Añade la columna para la relación
            $table->foreign('carpeta_id')->references('id')->on('carpetas')->onDelete('cascade'); // Establece la clave foránea
        });
        }

    public function down(): void
    {
        Schema::table('capturas', function (Blueprint $table) {
            $table->string('foto_modificada')->nullable();
            $table->dropForeign(['carpeta_id']);
            $table->dropColumn('carpeta_id');
        });
    }
};
