<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('planes_custom')) {
            Schema::create('planes_custom', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->decimal('precio_base', 10, 2);
                $table->integer('max_usuarios')->default(5);
                $table->integer('max_operaciones_mes')->nullable();
                $table->integer('max_documentos_mes')->nullable();
                $table->integer('max_modulaciones_mes')->nullable();
                $table->json('reportes_habilitados')->nullable();
                $table->json('features_habilitadas')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('suscripciones')) {
            Schema::create('suscripciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('plan_custom_id')->constrained('planes_custom')->cascadeOnDelete();
                $table->string('estado')->default('pendiente_pago');
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->decimal('monto_base', 10, 2);
                $table->decimal('monto_iva', 10, 2)->default(0);
                $table->decimal('monto_total', 10, 2);
                $table->string('referencia_pago')->unique();
                $table->string('comprobante_path')->nullable();
                $table->text('notas')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('configuracion_facturacion')) {
            Schema::create('configuracion_facturacion', function (Blueprint $table) {
                $table->id();
                $table->string('empresa_nombre')->default('NexaCore Aduanal');
                $table->string('empresa_rfc', 20)->nullable();
                $table->string('banco_nombre')->nullable();
                $table->string('banco_clabe', 20)->nullable();
                $table->string('banco_cuenta', 20)->nullable();
                $table->string('banco_referencia_prefix', 10)->default('NX');
                $table->integer('iva_porcentaje')->default(8);
                $table->string('email_notificaciones')->nullable();
                $table->string('logo_url')->nullable();
                $table->text('notas_legales')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
        Schema::dropIfExists('configuracion_facturacion');
        Schema::dropIfExists('planes_custom');
    }
};
