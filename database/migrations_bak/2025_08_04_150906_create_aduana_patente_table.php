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
        Schema::create('aduana_patente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patente_id')->constrained()->onDelete('cascade');
            $table->foreignId('aduana_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['patente_id', 'aduana_id']); // Evita duplicados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aduana_patente');
    }
};
