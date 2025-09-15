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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('general_ledger_id')->constrained()->cascadeOnDelete();

            // --- THE CRITICAL CHANGE ---
            // Replace the string 'account' with a foreign key
            $table->foreignId('chart_of_account_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
