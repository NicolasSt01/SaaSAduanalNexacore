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
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('cliente')->onDelete('cascade');
            $table->foreignId('patente_id')->constrained('patentes')->onDelete('cascade');
            $table->foreignId('aduana_id')->constrained('aduanas')->onDelete('cascade');

            // Número de pedimento 
            $table->string('numero_pedimento')->unique();

            //Tipo de expediente
            $table->enum('tipo_expediente', ['Unico', 'Consolidado'])->default('Unico');

            //Fechas
            $table->date('fecha_pago_pedimento')->nullable(); //Solo Cuando es Pedimento Unico
            $table->date('fecha_apertura')->nullable();         // Solo Cuando es Pedimento Consolidado
            $table->date('fecha_cierre')->nullable();           // Solo Cuando es Pedimento Consolidado

            // Categoría general (import/export/rectif)
            $table->enum('categoria', ['Importacion', 'Exportacion', 'Rectificaciones']);

            //Estado General
            $table->enum('estado', ['En proceso', 'Abierto', 'Cerrado', 'Cancelado'])->default('En proceso');

            //Observaciones
            $table->text('observaciones')->nullable();

            //Seguimiento de usuarios
            $table->foreignId('registrado_por')->constrained('users')->onDelete('cascade');
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->onDelete('set null');
            //$table->foreignId('documentador_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
