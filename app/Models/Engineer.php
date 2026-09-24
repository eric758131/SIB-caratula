<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Engineer extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'university_id',
        'rni',
        'name',
        'father_last_name',
        'mother_last_name',
        'ci',
        'phone',
        'email',
        'address',
        'sib_departmental',
        'suspension_start_date',
        'suspension_end_date',
        'image',
        'status',
    ];

    protected $casts = [
        'suspension_start_date' => 'date',
        'suspension_end_date'   => 'date',
    ];

    /* ============================================================
     |  ENUM: sib_departmental
     * ============================================================ */
    public const SIB_LA_PAZ      = 'la_paz';
    public const SIB_ORURO       = 'oruro';
    public const SIB_POTOSI      = 'potosi';
    public const SIB_COCHABAMBA  = 'cochabamba';
    public const SIB_TARIJA      = 'tarija';
    public const SIB_CHUQUISACA  = 'chuquisaca';
    public const SIB_SANTA_CRUZ  = 'santa_cruz';
    public const SIB_BENI        = 'beni';
    public const SIB_PANDO       = 'pando';

    public const SIB_DEPARTAMENTALS = [
        self::SIB_LA_PAZ,
        self::SIB_ORURO,
        self::SIB_POTOSI,
        self::SIB_COCHABAMBA,
        self::SIB_TARIJA,
        self::SIB_CHUQUISACA,
        self::SIB_SANTA_CRUZ,
        self::SIB_BENI,
        self::SIB_PANDO,
    ];

    public const SIB_DEPARTAMENTAL_LABELS = [
        self::SIB_LA_PAZ     => 'La Paz',
        self::SIB_ORURO      => 'Oruro',
        self::SIB_POTOSI     => 'Potosí',
        self::SIB_COCHABAMBA => 'Cochabamba',
        self::SIB_TARIJA     => 'Tarija',
        self::SIB_CHUQUISACA => 'Chuquisaca',
        self::SIB_SANTA_CRUZ => 'Santa Cruz',
        self::SIB_BENI       => 'Beni',
        self::SIB_PANDO      => 'Pando',
    ];

    /* ============================================================
     |  ENUM: status
     * ============================================================ */
    public const STATUS_ACTIVO                 = 'activo';
    public const STATUS_INACTIVO               = 'inactivo';
    public const STATUS_SUSPENSION_INDEFINIDA  = 'suspension_indefinida';
    public const STATUS_SUSPENSION_DEFINIDA    = 'suspension_definida';
    public const STATUS_EMERITO                = 'emerito';
    public const STATUS_FALLECIDO              = 'fallecido';

    public const STATUSES = [
        self::STATUS_ACTIVO,
        self::STATUS_INACTIVO,
        self::STATUS_SUSPENSION_INDEFINIDA,
        self::STATUS_SUSPENSION_DEFINIDA,
        self::STATUS_EMERITO,
        self::STATUS_FALLECIDO,
    ];

    public const STATUS_LABELS = [
        self::STATUS_ACTIVO                => 'Activo',
        self::STATUS_INACTIVO              => 'Inactivo',
        self::STATUS_SUSPENSION_INDEFINIDA => 'Suspensión indefinida',
        self::STATUS_SUSPENSION_DEFINIDA   => 'Suspensión definida',
        self::STATUS_EMERITO               => 'Emérito',
        self::STATUS_FALLECIDO             => 'Fallecido',
    ];

    /* ============================================================
     |  Relaciones
     * ============================================================ */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'engineers_specialties')
                    ->withTimestamps();
    }

    public function procedureProjectists(): HasMany
    {
        return $this->hasMany(ProcedureProjectist::class);
    }

    /* ============================================================
     |  Helpers
     * ============================================================ */

    public function getSibDepartmentalLabelAttribute(): string
    {
        return self::SIB_DEPARTAMENTAL_LABELS[$this->sib_departmental]
            ?? $this->sib_departmental;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status]
            ?? $this->status;
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->father_last_name} {$this->mother_last_name}");
    }

    public function isSuspended(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUSPENSION_INDEFINIDA,
            self::STATUS_SUSPENSION_DEFINIDA,
        ], true);
    }
}