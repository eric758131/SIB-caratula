<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureOwner extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_id',
        'owner_id',
        'is_principal',
    ];

    protected $casts = [
        'is_principal' => 'boolean',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }
}