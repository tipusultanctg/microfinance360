<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_repayment_schedules', function (Blueprint $table) {
            $table->decimal('amount_paid', 15, 2)->default(0.00)->after('total_amount');
            $table->decimal('balance', 15, 2)->virtualAs('total_amount - amount_paid')->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('loan_repayment_schedules', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'balance']);
        });
    }
};
