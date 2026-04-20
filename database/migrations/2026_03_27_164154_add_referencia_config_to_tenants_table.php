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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('referencia_prefijo', 10)->nullable()->after('configuracion');
            $table->unsignedInteger('referencia_consecutivo')->default(0)->after('referencia_prefijo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['referencia_prefijo', 'referencia_consecutivo']);
        });
    }
};
