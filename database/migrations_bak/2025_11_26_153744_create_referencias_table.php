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
        Schema::create('referencias', function (Blueprint $table) {
            $table->id();

            // Año en formato 2 dígitos (25, 26, 27...)
            $table->smallInteger('anio');

            // Contador que se reinicia cada año
            $table->bigInteger('contador')->default(0);

            $table->timestamps();

            // Evita duplicados del año
            $table->unique('anio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referencias');
    }
};
