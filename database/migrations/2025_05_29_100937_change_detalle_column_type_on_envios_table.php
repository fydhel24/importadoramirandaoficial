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
        // Asegúrate de tener instalada la dependencia doctrine/dbal para que esto funcione
        Schema::table('envios', function (Blueprint $table) {
            $table->text('detalle')->nullable()->change();
        });
    }

    public function down()
    {
        // Revertir a string (VARCHAR 255)
        Schema::table('envios', function (Blueprint $table) {
            $table->string('detalle')->nullable()->change();
        });
    }
};
