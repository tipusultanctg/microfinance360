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
        Schema::create('capital_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->comment('User who recorded the investment')->constrained()->cascadeOnDelete();

            // The cash/bank account where the capital was deposited
            $table->foreignId('asset_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();

            // The equity account that was credited
            $table->foreignId('equity_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();

            $table->date('investment_date');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capital_investments');
    }
};
