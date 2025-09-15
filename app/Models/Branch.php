<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory, BelongsToTenant; // <-- Add Trait

    protected $fillable = ['name', 'address','tenant_id'];
}
