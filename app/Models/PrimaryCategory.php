<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PrimaryCategory extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'image_1',
        'example',
        'image_2',
        'important_notes',
        'image_3',
        'logo_image',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Categoría especial: no usa terciaria, el usuario escribe un Topic.
     */
    public const NAME_INFORMES = 'INFORMES';

    public function isInformes(): bool
    {
        return mb_strtoupper(trim($this->name), 'UTF-8') === self::NAME_INFORMES;
    }

    public function secondaryCategories(): HasMany
    {
        return $this->hasMany(SecondaryCategory::class);
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