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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('expediente_id')->constrained('expedientes')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('cliente')->onDelete('cascade');
            $table->foreignId('patente_id')->constrained('patentes')->onDelete('cascade');
            
            // Información de la factura
            $table->string('numero_factura');
            $table->date('fecha_factura');
            $table->decimal('monto_total', 12, 2)->nullable();
            
            // Información de la semana
            $table->integer('year');
            $table->integer('semana');
            
            // Contabilidad de conceptos
            $table->integer('cantidad_tramites')->default(0);
            $table->integer('cantidad_rojos')->default(0);
            $table->integer('cantidad_sobrepesos')->default(0);
            $table->decimal('monto_adicionales', 10, 2)->default(0);
            $table->text('notas_adicionales')->nullable();
            
            // Usuario que registró
            $table->foreignId('registrado_por')->nullable()->constrained('users')->onDelete('set null');
            
            // Estado ampliado
            $table->enum('estado', ['pendiente', 'facturada', 'pagada', 'complemento_pago'])->default('pendiente');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['year', 'semana']);
            $table->index(['cliente_id', 'patente_id']);
            $table->index('estado');
            $table->index(['expediente_id', 'year', 'semana']);
            $table->index('numero_factura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
