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
            // Add the flag to identify super admins
            $table->boolean('is_superadmin')->default(false)->after('password');

            // Make tenant_id nullable so the super admin doesn't need to belong to a tenant
            $table->foreignId('tenant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_superadmin');
            $table->foreignId('tenant_id')->nullable(false)->change();
        });
    }
};
