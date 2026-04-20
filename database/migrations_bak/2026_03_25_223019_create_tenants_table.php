<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // Identificador interno: 'crosspoint', etc.
            $table->string('nombre_empresa');
            $table->string('rfc')->nullable();
            $table->string('correo_admin');
            $table->string('telefono')->nullable();
            $table->string('logo_url')->nullable();
            $table->enum('plan', ['basico', 'profesional', 'enterprise'])->default('basico');
            $table->enum('estado', ['activo', 'suspendido', 'cancelado'])->default('activo');
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('max_usuarios')->default(10);
            $table->integer('max_operaciones_mes')->nullable();
            $table->json('configuracion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};