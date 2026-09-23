<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Standard extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'description',
        'effective_date',
        'status',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'status' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function caratulas(): HasMany
    {
        return $this->hasMany(Caratula::class);
    }

    public function procedures(): BelongsToMany
    {
        return $this->belongsToMany(Procedure::class, 'procedure_standards')
                    ->withTimestamps();
    }
}