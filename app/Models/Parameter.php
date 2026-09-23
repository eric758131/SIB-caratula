<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_1',
        'example',
        'image_2',
        'important_notes',
        'image_3',
        'unit_of_measure',
        'parameter_type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function tertiaryCategories(): BelongsToMany
    {
        return $this->belongsToMany(TertiaryCategory::class, 'tariffs_parameters')
                    ->withPivot('tariff_index')
                    ->withTimestamps();
    }

    public function caratulas(): HasMany
    {
        return $this->hasMany(Caratula::class);
    }

    public function tariffParameters(): HasMany
    {
        return $this->hasMany(TariffParameter::class);
    }
}