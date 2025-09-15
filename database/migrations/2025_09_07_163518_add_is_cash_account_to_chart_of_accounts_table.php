<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->boolean('is_cash_account')->default(false)->after('type');
        });

        // Update the existing 'Cash' account to be a cash account
        DB::table('chart_of_accounts')->where('name', 'Cash')->update(['is_cash_account' => true]);
    }

    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropColumn('is_cash_account');
        });
    }
};
