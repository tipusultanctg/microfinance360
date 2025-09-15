<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            // Add a column to store the fee amount collected at disbursement
            $table->decimal('processing_fee', 10, 2)->default(0.00)->after('principal_amount');
        });
    }

    public function down(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->dropColumn('processing_fee');
        });
    }
};
