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
        Schema::table('exportaciones', function (Blueprint $table) {
            //
            $table->enum('prioridad', ['regular', 'media', 'urgente'])->default('regular')->after('documentador_id');
            $table->enum('estado', ['pendiente', 'proceso', 'terminado'])->default('pendiente')->after('prioridad');
            //$table->unsignedBigInteger('asignado_id')->nullable()->after('estado');

            //$table->foreign('asignado_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            //
            $table->dropForeign(['asignado_id']);
            $table->dropColumn(['prioridad', 'estado', 'asignado_id']);
        });
    }
};
