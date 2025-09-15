<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasJournalEntries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsClosure extends Model
{
    use HasFactory, BelongsToTenant, HasJournalEntries;

    protected $fillable = [
        'savings_account_id',
        'user_id',
        'closure_date',
        'final_interest_amount',
        'total_withdrawal_amount',
        'description',
    ];

    protected $casts = [
        'closure_date' => 'date',
        'final_interest_amount' => 'decimal:2',
        'total_withdrawal_amount' => 'decimal:2',
    ];

    public function savingsAccount(): BelongsTo
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
