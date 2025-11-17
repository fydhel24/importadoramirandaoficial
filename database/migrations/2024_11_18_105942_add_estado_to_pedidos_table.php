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
            $table->foreignId('id_sucursal')->nullable()->constrained('sucursales')->after('id_pedido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['id_sucursal']);
            $table->dropColumn('id_sucursal');
        });
    }
};
