<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyExpedientesUniqueConstraint extends Migration
{
    public function up()
    {
        // Eliminar la restricción única existente en numero_pedimento
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropUnique('expedientes_numero_pedimento_unique');
        });

        // Agregar nueva restricción única compuesta
        Schema::table('expedientes', function (Blueprint $table) {
            $table->unique(['numero_pedimento', 'patente_id'], 'expedientes_numero_pedimento_patente_id_unique');
        });
    }

    public function down()
    {
        // Revertir los cambios
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropUnique('expedientes_numero_pedimento_patente_id_unique');
        });

        Schema::table('expedientes', function (Blueprint $table) {
            $table->unique(['numero_pedimento'], 'expedientes_numero_pedimento_unique');
        });
    }
}