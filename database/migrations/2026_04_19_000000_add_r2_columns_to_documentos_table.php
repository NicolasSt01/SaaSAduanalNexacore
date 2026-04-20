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
        Schema::table('documentos', function (Blueprint $table) {
            if (!Schema::hasColumn('documentos', 'url_archivo')) {
                $table->text('url_archivo')->nullable()->after('ruta');
            }
            if (!Schema::hasColumn('documentos', 'peso')) {
                $table->unsignedBigInteger('peso')->nullable()->after('url_archivo');
            }
            if (!Schema::hasColumn('documentos', 'extension')) {
                $table->string('extension', 50)->nullable()->after('peso');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn(['url_archivo', 'peso', 'extension']);
        });
    }
};
