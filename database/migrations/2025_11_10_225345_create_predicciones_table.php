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
        Schema::create('predicciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->date('fecha');
            $table->float('ventas_predichas', 8, 2); // Puedes ajustar la precisión
            $table->float('error_promedio', 8, 4)->nullable();
            $table->float('conf', 8, 4)->nullable();
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predicciones');
    }
};
