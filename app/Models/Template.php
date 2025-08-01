<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'conteudo_padrao',
        'arquivo',
        'user_id',
    ];

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_template')
                    ->withTimestamps();
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_template')
                    ->withTimestamps();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'template_id');
    }
}
