<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasJournalEntries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapitalInvestment extends Model
{
    use HasFactory, BelongsToTenant, HasJournalEntries;

    protected $fillable = [
        'user_id',
        'asset_account_id',
        'equity_account_id',
        'investment_date',
        'amount',
        'description',
    ];

    protected $casts = [
        'investment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'asset_account_id');
    }

    public function equityAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'equity_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
