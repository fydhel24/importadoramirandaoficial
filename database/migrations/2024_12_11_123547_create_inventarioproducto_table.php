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
        Schema::create('inventarioproducto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_inventariohistorial');
            $table->unsignedBigInteger('id_producto'); // Campo separado para ID del producto
            $table->integer('cantidad'); // Campo separado para cantidad
            $table->integer('cantidad_antes')->nullable();
            $table->integer('cantidad_despues')->nullable();
            // Foreign keys
            $table->foreign('id_inventariohistorial')->references('id')->on('inventariohistorial');
            $table->foreign('id_producto')->references('id')->on('productos'); // Asegúrate de que la tabla 'productos' exista
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarioproducto');
    }

};
