<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_id',
        'tariff_parameter_id',
        'parameter_procedure_quantity',
        'unit_of_measure',
        'tariff_index',
        'parameter_procedure_subtotal',
    ];

    protected $casts = [
        'parameter_procedure_quantity' => 'decimal:2',
        'tariff_index' => 'decimal:6',
        'parameter_procedure_subtotal' => 'decimal:2',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function tariffParameter(): BelongsTo
    {
        return $this->belongsTo(TariffParameter::class);
    }
}