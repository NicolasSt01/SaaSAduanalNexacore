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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('Cliente')->after('email'); // puedes cambiar el valor por defecto si deseas
            $table->unsignedBigInteger('cliente_id')->nullable()->after('role'); // solo se usa si el usuario es externo (cliente)
            $table->boolean('active')->default(true)->after('deleted_at');
            
            // Si ya tienes una tabla clientes, puedes agregar la relación
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('role');
            $table->dropColumn('cliente_id');
        });
    }
};
