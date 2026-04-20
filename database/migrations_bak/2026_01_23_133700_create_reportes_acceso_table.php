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
        Schema::create('reportes_acceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('cliente')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->timestamp('expira_en');
            $table->integer('accesos')->default(0);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamps();

            $table->index(['token', 'expira_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_acceso');
    }
};
