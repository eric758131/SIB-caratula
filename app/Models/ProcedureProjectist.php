<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureProjectist extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_id',
        'engineer_id',
        'is_principal',
    ];

    protected $casts = [
        'is_principal' => 'boolean',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(Engineer::class);
    }
}