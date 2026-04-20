<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            // Cambiar patente_id a nullable
            $table->unsignedBigInteger('patente_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            // Revertir el cambio si es necesario
            $table->unsignedBigInteger('patente_id')->nullable(false)->change();
        });
    }
};