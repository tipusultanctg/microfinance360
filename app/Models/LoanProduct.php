<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'description',
        'interest_rate',
        'interest_method',
        'repayment_frequency',
        'max_loan_term',
        'late_payment_fee',
        'max_loan_amount',
        'processing_fee_type',  // <-- ADD THIS
        'processing_fee_value',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'late_payment_fee' => 'decimal:2',
    ];
}
