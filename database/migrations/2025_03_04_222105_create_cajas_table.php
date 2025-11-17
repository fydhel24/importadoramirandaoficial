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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha_apertura');
            $table->timestamp('fecha_cierre')->nullable();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_user_cierre')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('monto_inicial', 10, 2)->nullable();
            $table->decimal('efectivo_inicial', 10, 2)->nullable();
            $table->decimal('qr_inicial', 10, 2)->nullable();
            $table->decimal('monto_total', 10, 2)->nullable();
            $table->decimal('total_efectivo', 10, 2)->nullable();
            $table->decimal('total_qr', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cajas');
    }

};
