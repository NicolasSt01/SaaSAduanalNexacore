<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->foreignId('concepto_adicional_id')
                  ->nullable()
                  ->after('factura_id')
                  ->constrained('conceptos_adicionales')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['concepto_adicional_id']);
            $table->dropColumn('concepto_adicional_id');
        });
    }
};