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
            $table->decimal('pagado', 10, 2);  // Agrega el campo 'pagado'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('pagado');  // Elimina el campo 'pagado' si se revierte la migración
        });
    }
};
