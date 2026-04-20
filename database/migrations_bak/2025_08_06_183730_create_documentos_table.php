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
        Schema::create('documentos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('expediente_id')
              ->nullable()  // ← Esto hace que el campo sea opcional
              ->constrained()
              ->onDelete('cascade');
        $table->string('nombre_documento');
        $table->string('ruta_archivo');
        $table->string('tipo_documento')->nullable();
        $table->date('fecha_documento')->nullable();
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
