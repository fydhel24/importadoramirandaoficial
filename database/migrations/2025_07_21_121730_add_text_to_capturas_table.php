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
        Schema::table('capturas', function (Blueprint $table) {
            $table->text('campo_texto')->nullable(); // Agregar el campo 'campo_texto'
        });
    }

    /**
     * Revertir las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('capturas', function (Blueprint $table) {
            $table->dropColumn('campo_texto'); // Eliminar el campo 'campo_texto' si la migración se revierte
        });
    }
};
