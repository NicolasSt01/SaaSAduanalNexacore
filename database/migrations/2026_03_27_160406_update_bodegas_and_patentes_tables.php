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
        Schema::table('bodegas', function (Blueprint $table) {
            $table->dropForeign(['aduana_id']);
            $table->dropColumn('aduana_id');
            $table->string('tax_id', 50)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->text('domicilio')->nullable();
        });

        Schema::table('patentes', function (Blueprint $table) {
            $table->string('rfc', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bodegas', function (Blueprint $table) {
            $table->unsignedBigInteger('aduana_id')->nullable();
            $table->foreign('aduana_id')->references('id')->on('aduanas')->onDelete('cascade');
            $table->dropColumn(['tax_id', 'contacto', 'domicilio']);
        });

        Schema::table('patentes', function (Blueprint $table) {
            $table->dropColumn('rfc');
        });
    }
};
