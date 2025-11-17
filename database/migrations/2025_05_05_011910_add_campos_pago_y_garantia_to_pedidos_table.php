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
    Schema::table('pedidos', function (Blueprint $table) {
        $table->decimal('efectivo', 10, 2)->default(0);
        $table->decimal('transferencia_qr', 10, 2)->default(0);
        $table->enum('garantia', ['sin garantia', 'con garantia'])->nullable();
    });
}

public function down()
{
    Schema::table('pedidos', function (Blueprint $table) {
        $table->dropColumn(['efectivo', 'transferencia_qr', 'garantia']);
    });
}
};
