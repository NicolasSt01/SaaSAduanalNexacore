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
        Schema::table('exportaciones', function (Blueprint $table) {
            //
            $table->text('observaciones')->nullable()->after('estado');
            $table->boolean('sobrepeso')->default(false)->after('observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            //
            $table->dropColumn(['observaciones', 'sobrepeso']);
        });
    }
};
