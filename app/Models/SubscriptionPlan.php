<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'member_limit',
        'loan_limit',
        'features',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array', // Automatically handle JSON encoding/decoding
    ];

    /**
     * A subscription plan can be assigned to many tenants.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
