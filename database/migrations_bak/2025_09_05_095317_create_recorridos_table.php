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
        Schema::create('recorridos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exportacion_id');
            $table->string('origen', 150)->nullable();
            $table->string('destino', 150)->nullable();
            $table->string('ubicacion', 150);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->enum('estatus', ['transito', 'retraso', 'frontera'])->default('transito');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('exportacion_id')->references('id')->on('exportaciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorridos');
    }
};
