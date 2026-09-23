<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RequiredDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function tertiaryCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            TertiaryCategory::class,
            'required_documents_tertiary_categories',
            'required_document_id',
            'tertiary_category_id'
        )->withTimestamps();
    }
}