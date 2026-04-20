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
        // 1. TENANTS (Agencias aduanales clientes de NexaCore)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nombre_empresa');
            $table->string('rfc', 20)->nullable();
            $table->string('correo_admin');
            $table->string('telefono', 20)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->enum('plan', ['basico', 'profesional', 'enterprise'])->default('basico');
            $table->enum('estado', ['activo', 'suspendido', 'cancelado'])->default('activo');
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('max_usuarios')->default(10);
            $table->integer('max_operaciones_mes')->nullable();
            $table->json('configuracion')->nullable();
            $table->timestamps();
        });

        // 2. USERS (SaaS)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 50)->default('documentador'); // super_admin, admin, documentador, cliente
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->boolean('active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 3. CLIENTE
        Schema::create('cliente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('nombre');
            $table->string('rfc', 20)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo')->nullable();
            $table->text('direccion')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 4. DIRECTORIO (Contactos cliente)
        Schema::create('directorio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('cliente_id');
            $table->string('nombre');
            $table->string('puesto')->nullable();
            $table->string('correo')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->boolean('recibe_notificaciones')->default(true);
            $table->enum('canal_preferido', ['whatsapp', 'email', 'ambos'])->default('ambos');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('cascade');
        });

        // 5. IMPORTADORES
        Schema::create('importadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('nombre');
            $table->string('tax_id', 50)->nullable();
            $table->string('rfc', 20)->nullable();
            $table->string('pais', 100)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 6. ADUANAS (Global)
        Schema::create('aduanas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('clave', 10)->nullable();
            $table->timestamps();
        });

        // 7. BODEGAS
        Schema::create('bodegas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('nombre', 200);
            $table->unsignedBigInteger('aduana_id');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('aduana_id')->references('id')->on('aduanas')->onDelete('cascade');
        });

        // 8. PATENTES
        Schema::create('patentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('numero', 50);
            $table->string('nombre', 200);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 9. EXPEDIENTES
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('patente_id');
            $table->unsignedBigInteger('aduana_id');
            $table->string('numero_pedimento', 50);
            $table->enum('tipo_expediente', ['Unico', 'Consolidado'])->default('Unico');
            $table->date('fecha_pago_pedimento')->nullable();
            $table->date('fecha_apertura')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->enum('categoria', ['Importacion', 'Exportacion', 'Rectificaciones']);
            $table->text('observaciones')->nullable();
            $table->string('estado', 50)->default('En proceso');
            $table->unsignedBigInteger('registrado_por');
            $table->unsignedBigInteger('cerrado_por')->nullable();
            $table->string('clave_pedimento', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('cascade');
            $table->foreign('patente_id')->references('id')->on('patentes')->onDelete('cascade');
            $table->foreign('aduana_id')->references('id')->on('aduanas')->onDelete('cascade');
            $table->foreign('registrado_por')->references('id')->on('users');
            $table->foreign('cerrado_por')->references('id')->on('users');
        });

        // 10. OPERACIONES (Anteriormente exportaciones)
        Schema::create('operaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('referencia', 100)->nullable();
            $table->date('fecha_registro');
            $table->date('fecha_cruce_estimada')->nullable();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('importador_id')->nullable();
            $table->text('nombre_producto')->nullable();
            $table->unsignedBigInteger('bodega_id')->nullable();
            $table->string('num_factura', 100)->nullable();
            $table->unsignedBigInteger('aduana_id');
            $table->unsignedBigInteger('patente_id')->nullable();
            $table->unsignedBigInteger('expediente_id')->nullable();
            $table->string('num_thermo', 50)->nullable();
            $table->string('codigo_alpha', 50)->nullable();
            $table->string('num_doda', 100)->nullable();
            $table->string('modulacion', 100)->nullable();
            $table->timestamp('fecha_modulacion')->nullable();
            $table->unsignedBigInteger('usuario_registro_id');
            $table->unsignedBigInteger('usuario_cierre_id')->nullable();
            $table->string('prioridad', 20)->default('normal');
            $table->string('estado', 50)->default('capturada');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('cascade');
            $table->foreign('importador_id')->references('id')->on('importadores')->onDelete('set null');
            $table->foreign('bodega_id')->references('id')->on('bodegas')->onDelete('set null');
            $table->foreign('aduana_id')->references('id')->on('aduanas')->onDelete('cascade');
            $table->foreign('patente_id')->references('id')->on('patentes')->onDelete('set null');
            $table->foreign('expediente_id')->references('id')->on('expedientes')->onDelete('set null');
            $table->foreign('usuario_registro_id')->references('id')->on('users');
            $table->foreign('usuario_cierre_id')->references('id')->on('users');
        });

        // 11. DOCUMENTOS
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('pedimento_id')->nullable(); // Apunta a expedientes
            $table->unsignedBigInteger('operacion_id')->nullable(); // Apunta a operaciones
            $table->unsignedBigInteger('factura_id')->nullable();
            $table->unsignedBigInteger('concepto_adicional_id')->nullable();
            $table->string('nombre');
            $table->string('ruta', 500);
            $table->string('tipo_documento', 50);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('pedimento_id')->references('id')->on('expedientes')->onDelete('set null');
            $table->foreign('operacion_id')->references('id')->on('operaciones')->onDelete('set null');
        });

        // 12. OPERACION_HISTORIAL_DODA
        Schema::create('operacion_historial_doda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('operacion_id');
            $table->string('doda', 100);
            $table->string('estatus_anterior', 100)->nullable();
            $table->string('estatus_nuevo', 100);
            $table->boolean('hubo_cambio')->default(false);
            $table->json('respuesta_json')->nullable();
            $table->timestamp('consultado_at')->useCurrent();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('operacion_id')->references('id')->on('operaciones')->onDelete('cascade');
        });

        // 13. NOTIFICACIONES
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('tipo', 50);
            $table->string('titulo');
            $table->text('mensaje');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('operacion_id')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('operacion_id')->references('id')->on('operaciones')->onDelete('set null');
        });

        // 14. FACTURACION_NEXACORE (SaaS Billing)
        Schema::create('facturacion_nexacore', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('periodo', 7); // 2026-03
            $table->decimal('monto_renta', 10, 2);
            $table->decimal('monto_extras', 10, 2)->default(0);
            $table->decimal('monto_total', 10, 2);
            $table->enum('estado', ['pendiente', 'pagada', 'vencida', 'cancelada'])->default('pendiente');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->string('metodo_pago', 50)->nullable();
            $table->string('referencia_pago')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 15. CONCEPTOS_ADICIONALES (Legacy refactored)
        Schema::create('conceptos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('operacion_id');
            $table->string('tipo_concepto');
            $table->enum('ambito', ['operacion', 'camion'])->default('camion');
            $table->decimal('monto', 10, 2)->default(0);
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('operacion_id')->references('id')->on('operaciones')->onDelete('cascade');
        });

        // 16. FACTURAS (Legacy refactored)
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('expediente_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('patente_id');
            $table->string('numero_factura');
            $table->date('fecha_factura');
            $table->decimal('monto_total', 12, 2)->nullable();
            $table->integer('year');
            $table->integer('semana');
            $table->integer('cantidad_tramites')->default(0);
            $table->integer('cantidad_rojos')->default(0);
            $table->integer('cantidad_sobrepesos')->default(0);
            $table->decimal('monto_adicionales', 10, 2)->default(0);
            $table->text('notas_adicionales')->nullable();
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->enum('estado', ['pendiente', 'facturada', 'pagada', 'complemento_pago'])->default('pendiente');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('expediente_id')->references('id')->on('expedientes')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('cascade');
            $table->foreign('patente_id')->references('id')->on('patentes')->onDelete('cascade');
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('set null');
        });

        // 17. RECORRIDOS (Legacy)
        Schema::create('recorridos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('operacion_id');
            $table->string('latitud')->nullable();
            $table->string('longitud')->nullable();
            $table->string('ubicacion')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('operacion_id')->references('id')->on('operaciones')->onDelete('cascade');
        });

        // Sesiones y Caché
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('recorridos');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('conceptos_adicionales');
        Schema::dropIfExists('facturacion_nexacore');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('operacion_historial_doda');
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('operaciones');
        Schema::dropIfExists('expedientes');
        Schema::dropIfExists('patentes');
        Schema::dropIfExists('bodegas');
        Schema::dropIfExists('aduanas');
        Schema::dropIfExists('importadores');
        Schema::dropIfExists('directorio');
        Schema::dropIfExists('cliente');
        Schema::dropIfExists('users');
        Schema::dropIfExists('tenants');
    }
};