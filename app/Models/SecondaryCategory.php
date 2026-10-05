<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecondaryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'primary_category_id',
        'name',
        'description',
        'image_1',
        'example',
        'image_2',
        'important_notes',
        'image_3',
        'definition',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(PrimaryCategory::class);
    }

    public function tertiaryCategories(): HasMany
    {
        return $this->hasMany(TertiaryCategory::class);
    }

    public function caratulas(): HasMany
    {
        return $this->hasMany(Caratula::class);
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(Procedure::class);
    }
}