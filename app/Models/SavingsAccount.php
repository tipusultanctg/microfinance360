<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsAccount extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'member_id',
        'savings_product_id',
        'account_number',
        'balance',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function savingsProduct(): BelongsTo
    {
        return $this->belongsTo(SavingsProduct::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class)->latest('transaction_date');
    }
}
