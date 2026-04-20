<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Agrega campos para tracking de trial en tenants y usuarios.
     */
    public function up(): void
    {
        // Campos para tenant (trial)
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('trial_started_at')->nullable()->after('fecha_vencimiento');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            $table->boolean('es_trial')->default(false)->after('trial_ends_at');
        });

        // Campos para usuario (primer login)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['trial_started_at', 'trial_ends_at', 'es_trial']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'password_changed_at']);
        });
    }
};
