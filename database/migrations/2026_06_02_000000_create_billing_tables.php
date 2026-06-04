<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('planes')) {
            Schema::create('planes', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->decimal('precio_mensual', 10, 2);
                $table->integer('max_usuarios')->default(5);
                $table->integer('max_operaciones_mes')->nullable();
                $table->integer('max_documentos_mes')->nullable();
                $table->json('features')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('pagos')) {
            Schema::create('pagos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->decimal('monto', 10, 2);
                $table->date('fecha_pago');
                $table->string('metodo')->default('transferencia');
                $table->string('comprobante')->nullable();
                $table->string('periodo_inicio')->nullable();
                $table->string('periodo_fin')->nullable();
                $table->text('notas')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('facturas_billing')) {
            Schema::create('facturas_billing', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('pago_id')->nullable()->constrained('pagos')->nullOnDelete();
                $table->string('folio')->unique();
                $table->string('periodo');
                $table->decimal('monto', 10, 2);
                $table->string('estado')->default('pagada');
                $table->string('pdf_path')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('tenants', 'plan_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->foreignId('plan_id')->nullable()->after('plan')->constrained('planes')->nullOnDelete();
                $table->decimal('renta_mensual', 10, 2)->nullable()->after('plan_id');
                $table->integer('periodo_gracia_dias')->default(5)->after('renta_mensual');
                $table->date('fecha_corte')->nullable()->after('periodo_gracia_dias');
                $table->date('ultimo_pago_fecha')->nullable()->after('fecha_corte');
                $table->decimal('saldo_pendiente', 10, 2)->default(0)->after('ultimo_pago_fecha');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'plan_id')) {
                $table->dropConstrainedForeignId('plan_id');
            }
            $columns = ['renta_mensual', 'periodo_gracia_dias', 'fecha_corte', 'ultimo_pago_fecha', 'saldo_pendiente'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('tenants', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
