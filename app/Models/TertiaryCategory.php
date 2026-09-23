<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TertiaryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'secondary_category_id',
        'code',
        'name',
        'description',
        'image_1',
        'example',
        'image_2',
        'important_notes',
        'image_3',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function secondaryCategory(): BelongsTo
    {
        return $this->belongsTo(SecondaryCategory::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'specialty_categories')
                    ->withTimestamps();
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branches_categories')
                    ->withTimestamps();
    }

    public function parameters(): BelongsToMany
    {
        return $this->belongsToMany(Parameter::class, 'tariffs_parameters')
                    ->withPivot('tariff_index')
                    ->withTimestamps();
    }

    public function caratulas(): HasMany
    {
        return $this->hasMany(Caratula::class);
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(Procedure::class);
    }

    public function tariffParameters(): HasMany
    {
        return $this->hasMany(TariffParameter::class);
    }

    public function requiredDocuments(): BelongsToMany
    {
        return $this->belongsToMany(
            RequiredDocument::class,
            'required_documents_tertiary_categories',
            'tertiary_category_id',
            'required_document_id'
        )->withTimestamps();
    }  

    public function documents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'tertiary_categories_documents')
                    ->withTimestamps();
    }
}