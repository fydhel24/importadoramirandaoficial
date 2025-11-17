<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('carpetas', function (Blueprint $table) {
        $table->unsignedBigInteger('sucursal_id')->nullable()->after('fecha');
        $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('carpetas', function (Blueprint $table) {
        $table->dropForeign(['sucursal_id']);
        $table->dropColumn('sucursal_id');
    });
}

};
