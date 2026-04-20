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
        Schema::create('operacion_historial_doda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exportacion_id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('num_doda_anterior')->nullable();
            $table->string('num_doda_nuevo');
            $table->unsignedBigInteger('usuario_id');
            $table->text('motivo_cambio')->nullable();
            $table->timestamps();

            $table->foreign('exportacion_id')->references('id')->on('exportaciones')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operacion_historial_doda');
    }
};