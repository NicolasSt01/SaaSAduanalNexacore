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
        Schema::create('conceptos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operacion_id')
                  ->constrained('exportaciones')
                  ->onDelete('cascade');
            $table->string('tipo_concepto'); // sobrepeso, reacomodo_tarimas, etc.
            $table->enum('ambito', ['operacion', 'camion'])->default('camion');
            $table->decimal('monto', 10, 2)->default(0);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento en consultas
            $table->index('operacion_id');
            $table->index(['tipo_concepto', 'ambito']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos_adicionales');
    }
};