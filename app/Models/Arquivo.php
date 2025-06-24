<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Arquivo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'documento_id',
        'nome_original',
        'caminho',
        'tipo_mime',
        'tamanho',
        'tipo_arquivo',
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
