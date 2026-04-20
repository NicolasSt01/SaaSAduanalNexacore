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
        Schema::table('documentos', function (Blueprint $table) {
            // Agregar columna factura_id (NULLABLE - solo documentos de facturación la usarán)
            $table->foreignId('factura_id')
                  ->nullable()
                  ->after('exportacion_id')
                  ->constrained('facturas')
                  ->onDelete('cascade')
                  ->comment('Relación con factura de finanzas (null = documento normal)');
            
            // Índice para búsquedas rápidas de documentos por factura
            $table->index('factura_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['factura_id']);
            $table->dropIndex(['factura_id']);
            $table->dropColumn('factura_id');
        });
    }
};
