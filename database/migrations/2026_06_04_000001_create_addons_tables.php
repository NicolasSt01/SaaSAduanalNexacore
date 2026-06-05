<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('addons')) {
            Schema::create('addons', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->string('tipo');
                $table->string('identificador')->unique();
                $table->decimal('precio_mensual', 10, 2);
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('addons_contratados')) {
            Schema::create('addons_contratados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('addon_id')->constrained('addons')->cascadeOnDelete();
                $table->string('estado')->default('pendiente_pago');
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->decimal('monto_base', 10, 2);
                $table->decimal('monto_iva', 10, 2)->default(0);
                $table->decimal('monto_total', 10, 2);
                $table->string('referencia_pago')->unique();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('addons_contratados');
        Schema::dropIfExists('addons');
    }
};
