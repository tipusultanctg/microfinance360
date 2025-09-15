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
        Schema::create('savings_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('savings_account_id')->unique()->constrained()->cascadeOnDelete(); // An account can only be closed once
            $table->foreignId('user_id')->comment('User who processed closure')->constrained()->cascadeOnDelete();

            $table->date('closure_date');
            $table->decimal('final_interest_amount', 15, 2)->default(0.00);
            $table->decimal('total_withdrawal_amount', 15, 2);
            $table->text('description')->nullable();

            // We will link the specific transactions to this record later if needed
            // For now, the journal entry link is sufficient

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_closures');
    }
};
