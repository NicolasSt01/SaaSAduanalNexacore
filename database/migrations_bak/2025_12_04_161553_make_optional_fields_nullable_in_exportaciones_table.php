<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            // Modificar campos para permitir valores nulos
            $table->unsignedBigInteger('bodega_id')->nullable()->change();
            $table->unsignedBigInteger('patente_id')->nullable()->change();
            $table->unsignedBigInteger('expediente_id')->nullable()->change();
            $table->string('num_thermo', 50)->nullable()->change();
            $table->string('codigo_alpha', 20)->nullable()->change();
            $table->string('num_doda', 50)->nullable()->change();
            $table->string('modulacion')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            //
            // Revertir los cambios (hacer los campos NOT NULL nuevamente)
            // PRECAUCIÓN: Esto fallará si hay valores NULL en la base de datos
            $table->unsignedBigInteger('bodega_id')->nullable(false)->change();
            $table->unsignedBigInteger('patente_id')->nullable(false)->change();
            $table->unsignedBigInteger('expediente_id')->nullable(false)->change();
            $table->string('num_thermo', 50)->nullable(false)->change();
            $table->string('codigo_alpha', 20)->nullable(false)->change();
            $table->string('num_doda', 50)->nullable(false)->change();
            $table->string('modulacion')->nullable(false)->change();
        });
    }
};
