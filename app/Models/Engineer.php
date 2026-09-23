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
        'suspension_end_date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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
}