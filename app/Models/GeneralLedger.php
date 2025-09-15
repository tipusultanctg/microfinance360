<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneralLedger extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'transactionable_type',
        'transactionable_id',
        'date',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
