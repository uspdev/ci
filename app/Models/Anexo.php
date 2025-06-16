<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anexo extends Model
{
    use HasFactory;

    protected $fillable = [
        'documento_id',
        'nome_original',
        'caminho',
        'tipo_mime',
        'tamanho',
        'tipo_anexo',
        'user_id',
    ];

    protected $casts = [
        'tamanho' => 'integer',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }
}
