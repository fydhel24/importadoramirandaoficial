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
        Schema::table('inventario', function (Blueprint $table) {
            $table->boolean('favorito')->default(false); // Campo para marcar si es favorito
        });
    }

    public function down()
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->dropColumn('favorito');
        });
    }
};
