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
        Schema::create('prod_registro_mal_estado', function (Blueprint $table) {
            $table->id();
            $table->string('celular');
            $table->string('persona');
            $table->enum('departamento', [
                'La Paz', 'Cochabamba', 'Santa Cruz',
                'Oruro', 'Potosí', 'Chuquisaca',
                'Tarija', 'Beni', 'Pando'
            ]);
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->enum('estado', ['mal', 'bueno'])->default('mal');
            $table->text('descripcion_problema')->nullable();
            $table->timestamp('fecha_inscripcion')->nullable();
            $table->timestamp('fecha_cambio_estado')->nullable();

            // Checkboxes (booleanos)
            $table->boolean('checkbox')->default(false);
            $table->boolean('de_la_paz')->default(false);
            $table->boolean('enviado')->default(false);
            $table->boolean('extra1')->default(false);
            $table->boolean('extra2')->default(false);
            $table->boolean('extra3')->default(false);
            $table->boolean('extra4')->default(false);
            $table->boolean('extra5')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prod_registro_mal_estado');
    }
};
