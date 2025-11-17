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
    Schema::table('permissions', function (Blueprint $table) {
        $table->string('descripcion')->after('name'); // Agregar la columna 'descripcion'
    });
}

    /**
     * Reverse the migrations.
     */
   public function down()
{
    Schema::table('permissions', function (Blueprint $table) {
        $table->dropColumn('descripcion'); // Eliminar la columna 'descripcion'
    });
}
};
