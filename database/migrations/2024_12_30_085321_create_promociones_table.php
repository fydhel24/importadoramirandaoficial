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
        // Eliminar la tabla si ya existe 
        Schema::dropIfExists('promociones');


        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('precio_promocion', 10, 2); // Precio de la promoción 
            $table->foreignId('id_sucursal')->constrained('sucursales'); // Sucursal asociada 
            $table->foreignId('id_usuario')->constrained('users'); // Usuario que creó la promoción 
            $table->date('fecha_inicio'); // Fecha de inicio de la promoción 
            $table->date('fecha_fin'); // Fecha de fin de la promoción 
            $table->boolean('estado')->default(true); // Activo o no activo 
            $table->timestamps();
        });
    }
    /** 
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};
