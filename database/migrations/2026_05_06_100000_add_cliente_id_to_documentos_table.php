<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            // Soporte para documentos a nivel cliente (INC-019)
            $table->unsignedBigInteger('cliente_id')->nullable()->after('concepto_adicional_id');
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('cascade');
            
            // Fecha de vigencia para documentos con caducidad (ej. CSF)
            $table->date('fecha_vencimiento')->nullable()->after('extension');
            
            // Índice para búsquedas rápidas por cliente + tipo
            $table->index(['cliente_id', 'tipo_documento']);
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropIndex(['cliente_id', 'tipo_documento']);
            $table->dropColumn(['cliente_id', 'fecha_vencimiento']);
        });
    }
};
