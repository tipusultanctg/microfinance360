<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Member extends Model implements HasMedia
{
    use HasFactory, BelongsToTenant, InteractsWithMedia;

    protected $fillable = [
        'branch_id',
        'member_uid',
        'name',
        'phone',
        'date_of_birth',
        'status',
        'father_name',
        'mother_name',
        'gender',
        'marital_status',
        'spouse',
        'present_address',
        'permanent_address',
        'workplace',
        'occupation',
        'religion',
        'registration_date',
    ];

    /**
     * A member belongs to a branch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Define the media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('member_photo')
            ->singleFile() // Ensures only one photo can be attached
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);

        $this->addMediaCollection('kyc_documents')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }

    /**
     * Register conversions for the member_photo collection.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10);
    }

    public function savingsAccounts()
    {
        return $this->hasMany(SavingsAccount::class);
    }

    public function loanAccounts()
    {
        return $this->hasMany(LoanAccount::class);
    }
}
