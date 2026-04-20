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
            //
            $table->foreignId('exportacion_id')
                  ->nullable()
                  ->constrained('exportaciones')
                  ->onDelete('cascade')
                  ->after('expediente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            //
            $table->dropForeign(['exportacion_id']);
            $table->dropColumn('exportacion_id');
        });
    }
};
