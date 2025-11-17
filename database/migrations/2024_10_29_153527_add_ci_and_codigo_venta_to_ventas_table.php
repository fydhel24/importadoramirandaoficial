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
               $table->string('ci')->nullable(); // Campo CI puede estar vacío
            $table->string('codigo_venta')->unique()->nullable(); // Campo codigo_venta único y puede estar vacío
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
             $table->dropColumn(['ci', 'codigo_venta']);
      
        });
    }
};
