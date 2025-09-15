<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasJournalEntries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LoanApplication extends Model implements HasMedia
{
    use HasFactory, BelongsToTenant, HasJournalEntries, InteractsWithMedia;

    protected $fillable = [
        'member_id',
        'loan_product_id',
        'requested_amount',
        'requested_term',
        'purpose',
        'status',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('loan_documents')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }
}
