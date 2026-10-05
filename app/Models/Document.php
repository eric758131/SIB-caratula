<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'hash_code',
        'document_type',
        'other_field',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public const DOCUMENT_TYPE_MEMORIA_CALCULO = 'memoria_calculo';
    public const DOCUMENT_TYPE_CARTA_AUTORIZACION = 'carta_autorizacion';
    public const DOCUMENT_TYPE_PLANO_ESTRUCTURAL = 'plano_estructural';
    public const DOCUMENT_TYPE_PLANO_SANITARIO = 'plano_sanitario';
    public const DOCUMENT_TYPE_PLANO_ARQUITECTONICO = 'plano_arquiteconico';
    public const DOCUMENT_TYPE_PLANO_ELECTRICO = 'plano_electrico';
    public const DOCUMENT_TYPE_PLANO_TECNICO = 'plano_tecnico';
    public const DOCUMENT_TYPE_OTRO = 'otro';

    public const DOCUMENT_TYPES = [
        self::DOCUMENT_TYPE_MEMORIA_CALCULO,
        self::DOCUMENT_TYPE_CARTA_AUTORIZACION,
        self::DOCUMENT_TYPE_PLANO_ESTRUCTURAL,
        self::DOCUMENT_TYPE_PLANO_SANITARIO,
        self::DOCUMENT_TYPE_PLANO_ARQUITECTONICO,
        self::DOCUMENT_TYPE_PLANO_ELECTRICO,
        self::DOCUMENT_TYPE_PLANO_TECNICO,
        self::DOCUMENT_TYPE_OTRO,
    ];

    public const STATUS_ACTIVO = 'activo';
    public const STATUS_INACTIVO = 'inactivo';
    public const STATUS_OBSERVADO = 'observado';
    public const STATUS_ANULADO = 'anulado';

    public const STATUSES = [
        self::STATUS_ACTIVO,
        self::STATUS_INACTIVO,
        self::STATUS_OBSERVADO,
        self::STATUS_ANULADO,
    ];

    public function procedureDocuments(): HasMany
    {
        return $this->hasMany(ProcedureDocument::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }
}