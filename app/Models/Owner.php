<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function caratulas(): HasMany
    {
        return $this->hasMany(Caratula::class);
    }

    public function procedureOwners(): HasMany
    {
        return $this->hasMany(ProcedureOwner::class);
    }
}