<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureDetail extends Model
{
    use HasFactory;

    protected $table = 'procedures_details';

    protected $fillable = [
        'procedure_id',
        'tertiary_category_parameter_id',
        'numeric_value',
        'text_value',
        'date_value',
        'boolean_value',
        'unit_of_measure',
        'tariff_index',
        'subtotal',
    ];

    protected $casts = [
        'numeric_value' => 'decimal:6',
        'date_value'    => 'date',
        'boolean_value' => 'boolean',
        'tariff_index'  => 'decimal:6',
        'subtotal'      => 'decimal:2',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function tertiaryCategoryParameter(): BelongsTo
    {
        return $this->belongsTo(TertiaryCategoryParameter::class);
    }

    /**
     * Valor ingresado según el tipo de dato del parámetro (solo una columna tiene valor).
     */
    public function getValueAttribute(): mixed
    {
        return match ($this->tertiaryCategoryParameter?->parameter?->data_type) {
            Parameter::DATA_NUMERO   => $this->numeric_value !== null ? (float) $this->numeric_value : null,
            Parameter::DATA_FECHA    => $this->date_value?->format('Y-m-d'),
            Parameter::DATA_BOOLEANO => $this->boolean_value,
            Parameter::DATA_TEXTO    => $this->text_value,
            // Sin parámetro enlazado: la columna que tenga valor
            default => $this->text_value
                ?? ($this->numeric_value !== null ? (float) $this->numeric_value : null)
                ?? $this->date_value?->format('Y-m-d')
                ?? $this->boolean_value,
        };
    }
}
