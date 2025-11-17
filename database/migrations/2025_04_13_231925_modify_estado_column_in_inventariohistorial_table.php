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
        Schema::table('inventariohistorial', function (Blueprint $table) {
            // Modificar la columna 'estado' para que sea de tipo string
            $table->string('estado', 50)->default('enviado')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventariohistorial', function (Blueprint $table) {
            // Si quieres revertirlo, deberías cambiar el tipo de nuevo a enum
            $table->enum('estado', ['pendiente', 'enviado'])->default('enviado')->change();
        });
    }

};
