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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion');
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_descuento', 10, 2)->nullable(); // Nuevo campo
            $table->integer('stock');
            $table->boolean('estado');
            $table->date('fecha');
            $table->foreignId('id_cupo')->nullable()->constrained('cupos');
            $table->foreignId('id_tipo')->constrained('tipos');
            $table->foreignId('id_categoria')->constrained('categorias');
            $table->foreignId('id_marca')->constrained('marcas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
