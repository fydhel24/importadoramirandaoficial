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
        Schema::create('pagos_empleados', function (Blueprint $table) {
            $table->id();
            $table->string('mes');
            $table->string('año');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->decimal('bono_extra', 10, 2)->nullable();
            $table->decimal('descuento', 10, 2)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_empleados');
    }
};
