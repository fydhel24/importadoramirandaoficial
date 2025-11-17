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
        Schema::table('sucursales', function (Blueprint $table) {
            $table->string('logo')->nullable();  // Logo de la sucursal
            $table->string('celular')->nullable();  // Celular de la sucursal
            $table->string('estado')->nullable();  // Estado de la sucursal
        });
    }

    /**
     * Reverse the migrations.
     */
       public function down()
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn(['logo', 'celular', 'estado']);
        });
    }
};

