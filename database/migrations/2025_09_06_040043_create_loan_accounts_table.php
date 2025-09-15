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
        Schema::create('loan_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_product_id')->constrained()->cascadeOnDelete();

            $table->decimal('principal_amount', 15, 2);
            $table->decimal('total_interest', 15, 2);
            $table->decimal('total_payable', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('balance', 15, 2);

            $table->integer('term')->comment('Number of installments');
            $table->timestamp('disbursement_date');
            $table->string('status')->default('active'); // active, paid, overdue, closed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_accounts');
    }
};
