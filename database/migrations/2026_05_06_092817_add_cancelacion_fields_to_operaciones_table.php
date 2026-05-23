<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->string('motivo_cancelacion')->nullable()->after('observaciones');
            $table->timestamp('fecha_cancelacion')->nullable()->after('motivo_cancelacion');
            $table->unsignedBigInteger('usuario_cancelacion_id')->nullable()->after('fecha_cancelacion');

            $table->foreign('usuario_cancelacion_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->dropForeign(['usuario_cancelacion_id']);
            $table->dropColumn(['motivo_cancelacion', 'fecha_cancelacion', 'usuario_cancelacion_id']);
        });
    }
};