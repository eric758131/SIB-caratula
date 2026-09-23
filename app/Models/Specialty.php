<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function engineers(): BelongsToMany
    {
        return $this->belongsToMany(Engineer::class, 'engineers_specialties')
                    ->withTimestamps();
    }

    public function tertiaryCategories(): BelongsToMany
    {
        return $this->belongsToMany(TertiaryCategory::class, 'specialty_categories')
                    ->withTimestamps();
    }

    public function caratulas(): HasMany
    {
        return $this->hasMany(Caratula::class);
    }
}