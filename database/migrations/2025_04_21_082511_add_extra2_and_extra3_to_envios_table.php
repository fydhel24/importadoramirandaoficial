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
        // Agregar los campos 'extra2' y 'extra3'
        Schema::table('envios', function (Blueprint $table) {
            $table->boolean('extra2')->nullable()->default(false);
            $table->boolean('extra3')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar los campos 'extra2' y 'extra3' si se revierte la migración
        Schema::table('envios', function (Blueprint $table) {
            $table->dropColumn(['extra2', 'extra3']);
        });
    }

};
