<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'abreviacao',
        'grupo_id',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(Template::class, 'categoria_template')
                    ->withTimestamps();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'categoria_id');
    }
}
