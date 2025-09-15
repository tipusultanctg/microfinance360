<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsProduct extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'description',
        'interest_rate',
        'interest_posting_frequency',
        'min_balance_for_interest',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'min_balance_for_interest' => 'decimal:2',
    ];
}
