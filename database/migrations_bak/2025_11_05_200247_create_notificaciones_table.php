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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            
            // Usuario que recibe la notificación
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Tipo de notificación
            $table->enum('tipo', [
                'documento_subido',
                'operacion_completada',
                'modulacion_actualizada',
                'concepto_agregado',
                'estatus_actualizado',
                'Operacion_en_Proceso',
            ]);
            
            // Mensaje de la notificación
            $table->string('titulo');
            $table->text('mensaje');
            
            // Referencia a la operación/exportación
            $table->foreignId('exportacion_id')->nullable()->constrained('exportaciones')->onDelete('cascade');
            
            // Datos adicionales (JSON)
            $table->json('datos')->nullable(); // Para thermo, factura, etc.
            
            // Usuario que generó la acción
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Estado de lectura
            $table->boolean('leida')->default(false);
            $table->timestamp('leida_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'leida']);
            $table->index('tipo');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
