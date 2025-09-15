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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('interest_rate', 5, 2)->comment('Annual interest rate');
            $table->enum('interest_method', ['flat', 'reducing']);
            $table->enum('repayment_frequency', ['daily', 'weekly', 'monthly']);
            $table->integer('max_loan_term')->comment('In number of repayments (e.g., 12 for 12 months)');
            $table->decimal('late_payment_fee', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
