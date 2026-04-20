<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exportaciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('cliente_id')->constrained('cliente');
            $table->foreignId('importador_id')->constrained('importadores');
            $table->string('nombre_producto');
            $table->foreignId('bodega_id')->constrained('bodegas');
            $table->string('num_factura');
            $table->foreignId('aduana_id')->constrained('aduanas');
            $table->foreignId('patente_id')->constrained('patentes');
            $table->foreignId('expediente_id')->constrained('expedientes');
            $table->string('num_thermo')->nullable();
            $table->string('codigo_alpha')->nullable();
            $table->string('num_doda')->nullable();
            $table->string('modulacion')->nullable();
            $table->foreignId('documentador_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exportaciones');
    }
};