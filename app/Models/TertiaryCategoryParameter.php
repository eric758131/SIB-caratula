<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Parámetro asignado a una categoría terciaria, con su índice tarifario.
 */
class TertiaryCategoryParameter extends Model
{
    use HasFactory;

    protected $table = 'tertiary_categories_parameters';

    protected $fillable = [
        'tertiary_category_id',
        'parameter_id',
        'tariff_index',
        'is_required',
        'status',
    ];

    protected $casts = [
        'tariff_index' => 'decimal:6',
        'is_required'  => 'boolean',
        'status'       => 'boolean',
    ];

    public function tertiaryCategory(): BelongsTo
    {
        return $this->belongsTo(TertiaryCategory::class);
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    public function procedureDetails(): HasMany
    {
        return $this->hasMany(ProcedureDetail::class);
    }
}
