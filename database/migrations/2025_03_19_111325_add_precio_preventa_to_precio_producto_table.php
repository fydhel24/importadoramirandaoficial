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
        Schema::table('precio_producto', function (Blueprint $table) {
            // Agregar el nuevo campo 'precio_preventa'
            $table->decimal('precio_preventa', 10, 2)->nullable()->after('precio_extra');
        });
    }

    public function down()
    {
        Schema::table('precio_producto', function (Blueprint $table) {
            // Eliminar el campo 'precio_preventa' si se revierte la migración
            $table->dropColumn('precio_preventa');
        });
    }
};
