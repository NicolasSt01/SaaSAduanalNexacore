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
        Schema::create('notificaciones_sistema', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable()->index(); // Null = notificación global
            $table->string('tipo')->index(); // 'bot_limit_reached', 'bot_near_limit', 'resource_limit', etc.
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('accion_url')->nullable(); // URL del botón de acción
            $table->string('accion_texto')->nullable(); // Texto del botón (ej: "Actualizar Plan")
            $table->enum('nivel', ['info', 'warning', 'error', 'success'])->default('info')->index();
            $table->boolean('leida')->default(false)->index();
            $table->timestamp('leida_en')->nullable();
            $table->json('metadata')->nullable(); // Datos adicionales
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_sistema');
    }
};
