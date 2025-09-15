<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasJournalEntries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoanAccount extends Model
{
    use HasFactory, BelongsToTenant, HasJournalEntries;

    protected $fillable = [
        'account_number',
        'loan_application_id',
        'member_id',
        'loan_product_id',
        'principal_amount',
        'total_interest',
        'total_payable',
        'amount_paid',
        'balance',
        'term',
        'disbursement_date',
        'status',
        'processing_fee',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_payable' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'disbursement_date' => 'datetime',
        'processing_fee' => 'decimal:2',
    ];

    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(LoanRepaymentSchedule::class)->orderBy('due_date', 'asc');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class)->latest('payment_date');
    }
}
