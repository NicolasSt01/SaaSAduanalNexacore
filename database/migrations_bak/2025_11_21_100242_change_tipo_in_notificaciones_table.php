<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            // Cambiar de enum a string (máximo 50 caracteres, por ejemplo)
            $table->string('tipo', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            // Si necesitas revertir (vuelve a enum con tus valores actuales)
            $table->enum('tipo', [
                'documento_subido',
                'operacion_completada',
                'modulacion_actualizada',
                'concepto_agregado',
                'estatus_actualizado',
                'Operacion_en_Proceso',
            ])->change();
        });
    }
};
