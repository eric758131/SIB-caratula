<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function tertiaryCategories(): BelongsToMany
    {
        return $this->belongsToMany(TertiaryCategory::class, 'branches_categories')
                    ->withTimestamps();
    }

    public function engineers(): HasMany
    {
        return $this->hasMany(Engineer::class);
    }

    public function caratulas(): HasMany
    {
        return $this->hasMany(Caratula::class);
    }
}