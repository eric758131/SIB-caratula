<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Archivo que el usuario sube a un trámite.
 * required_document_id = documento requerido al que responde (null = documento adicional).
 */
class ProcedureDocument extends Model
{
    use HasFactory;

    protected $table = 'procedures_documents';

    protected $fillable = [
        'document_id',
        'procedure_id',
        'required_document_id',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function requiredDocument(): BelongsTo
    {
        return $this->belongsTo(RequiredDocument::class);
    }
}
