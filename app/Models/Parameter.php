<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'unit_of_measure',
        'parameter_type',
        'data_type',
        'max_decimals',
        'status',
    ];

    protected $casts = [
        'status'       => 'boolean',
        'max_decimals' => 'integer',
    ];

    /* ============================================================
     |  ENUM: parameter_type (clasificación funcional)
     * ============================================================ */
    public const TYPE_PARAMETRICO = 'parametrico';
    public const TYPE_CARATULA    = 'caratula';
    public const TYPE_JUDICIAL    = 'judicial';
    public const TYPE_DIRECCIONAL = 'direccional';

    public const TYPES = [
        self::TYPE_PARAMETRICO,
        self::TYPE_CARATULA,
        self::TYPE_JUDICIAL,
        self::TYPE_DIRECCIONAL,
    ];

    public const TYPE_LABELS = [
        self::TYPE_PARAMETRICO => 'Paramétrico',
        self::TYPE_CARATULA    => 'Carátula',
        self::TYPE_JUDICIAL    => 'Judicial',
        self::TYPE_DIRECCIONAL => 'Direccional',
    ];

    /* ============================================================
     |  ENUM: data_type (tipo de dato que se ingresa)
     * ============================================================ */
    public const DATA_NUMERO   = 'numero';
    public const DATA_TEXTO    = 'texto';
    public const DATA_FECHA    = 'fecha';
    public const DATA_BOOLEANO = 'booleano';

    public const DATA_TYPES = [
        self::DATA_NUMERO,
        self::DATA_TEXTO,
        self::DATA_FECHA,
        self::DATA_BOOLEANO,
    ];

    public const DATA_TYPE_LABELS = [
        self::DATA_NUMERO   => 'Número',
        self::DATA_TEXTO    => 'Texto',
        self::DATA_FECHA    => 'Fecha',
        self::DATA_BOOLEANO => 'Sí / No',
    ];

    public function getParameterTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->parameter_type] ?? $this->parameter_type;
    }

    public function getDataTypeLabelAttribute(): string
    {
        return self::DATA_TYPE_LABELS[$this->data_type] ?? $this->data_type;
    }
}
