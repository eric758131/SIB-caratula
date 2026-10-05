<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Procedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',  
        'primary_category_id',
        'secondary_category_id',
        'tertiary_category_id',
        'hash_code',
        'number',
        'entry_date',
        'title',
        'address',
        'latitude',
        'longitude',
        'zone',
        'municipality',
        'bill_amount',
        'bill_amount_literal',
        'total_quote_amount',
        'observations',
        'memories_quantity',
        'has_plans',
        'plans_quantity',
        'plans_copies_quantity',
        'plans_total_quantity',
        'copies_total_quantity',
        'sticker_quantity',
        'procedure_type',
        'validity_start',
        'validity_end',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'validity_start' => 'date',
        'validity_end' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'bill_amount' => 'decimal:2',
        'total_quote_amount' => 'decimal:2',
        'has_plans' => 'boolean',
        'memories_quantity' => 'integer',
        'plans_quantity' => 'integer',
        'plans_copies_quantity' => 'integer',
        'plans_total_quantity' => 'integer',
        'copies_total_quantity' => 'integer',
        'sticker_quantity' => 'integer',
    ];

    public const PROCEDURE_TYPE_REGISTRO_INICIAL = 'registro_inicial';
    public const PROCEDURE_TYPE_COPIA_LEGALIZADA = 'copia_legalizada';
    public const PROCEDURE_TYPE_ACTUALIZACION_DATOS = 'actualizacion_datos';
    public const PROCEDURE_TYPE_ACTUALIZACION_PARAMETROS = 'actualizacion_parametros';
    public const PROCEDURE_TYPE_ACTUALIZACION_TECNICA = 'actualizacion_tecnica';

    public const PROCEDURE_TYPES = [
        self::PROCEDURE_TYPE_REGISTRO_INICIAL,
        self::PROCEDURE_TYPE_COPIA_LEGALIZADA,
        self::PROCEDURE_TYPE_ACTUALIZACION_DATOS,
        self::PROCEDURE_TYPE_ACTUALIZACION_PARAMETROS,
        self::PROCEDURE_TYPE_ACTUALIZACION_TECNICA,
    ];

    public const STATUS_PENDIENTE = 'pendiente';
    public const STATUS_REGISTRADO = 'registrado';
    public const STATUS_COTIZADO = 'cotizado';
    public const STATUS_DESIGNADO = 'designado';
    public const STATUS_EN_VERIFICACION = 'en_verificacion';
    public const STATUS_OBSERVADO = 'observado';
    public const STATUS_EN_CORRECCION = 'en_correccion';
    public const STATUS_CORREGIDO = 'corregido';
    public const STATUS_ANULADO = 'anulado';
    public const STATUS_APROBADO = 'aprobado';
    public const STATUS_POR_PAGAR = 'por_pagar';
    public const STATUS_PAGADO = 'pagado';
    public const STATUS_POR_VISAR = 'por_visar';
    public const STATUS_VISADO = 'visado';
    public const STATUS_NOTIFICADO = 'notificado';
    public const STATUS_EN_RECEPCION = 'en_recepcion';
    public const STATUS_ENTREGADO = 'entregado';

    public const STATUSES = [
        self::STATUS_PENDIENTE,
        self::STATUS_REGISTRADO,
        self::STATUS_COTIZADO,
        self::STATUS_DESIGNADO,
        self::STATUS_EN_VERIFICACION,
        self::STATUS_OBSERVADO,
        self::STATUS_EN_CORRECCION,
        self::STATUS_CORREGIDO,
        self::STATUS_ANULADO,
        self::STATUS_APROBADO,
        self::STATUS_POR_PAGAR,
        self::STATUS_PAGADO,
        self::STATUS_POR_VISAR,
        self::STATUS_VISADO,
        self::STATUS_NOTIFICADO,
        self::STATUS_EN_RECEPCION,
        self::STATUS_ENTREGADO,
    ];

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(PrimaryCategory::class);
    }

    public function secondaryCategory(): BelongsTo
    {
        return $this->belongsTo(SecondaryCategory::class);
    }

    public function tertiaryCategory(): BelongsTo
    {
        return $this->belongsTo(TertiaryCategory::class);
    }

    public function standards(): BelongsToMany
    {
        return $this->belongsToMany(Standard::class, 'procedure_standards')
                    ->withTimestamps();
    }

    public function details(): HasMany
    {
        return $this->hasMany(ProcedureDetail::class);
    }

    /** Archivos que subió el usuario (cada uno es un Document con su archivo en Media Library) */
    public function documents(): HasMany
    {
        return $this->hasMany(ProcedureDocument::class);
    }

    public function projectists(): HasMany
    {
        return $this->hasMany(ProcedureProjectist::class);
    }

    public function procedureOwners(): HasMany
    {
        return $this->hasMany(ProcedureOwner::class);
    }

    public function principalProjectist()
    {
        return $this->hasOne(ProcedureProjectist::class)->where('is_principal', true);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Mientras está pendiente es un borrador del asistente: se puede editar y subir documentos.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_PENDIENTE;
    }
}