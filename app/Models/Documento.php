<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Documento extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'codigo',
        'destinatario',
        'remetente',
        'data_documento',
        'assunto',
        'mensagem',
        'finalizado',
        'data_finalizacao',
        'anexo_id',
        'categoria_id',
        'user_id',
        'finalizer_user_id',
        'grupo_id',
        'template_id',
    ];

    protected $casts = [
        'data_documento' => 'date',
        'data_finalizacao' => 'datetime',
        'finalizado' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly([
        'codigo',
        'destinatario',
        'remetente',
        'data_documento',
        'assunto',
        'mensagem',
        'finalizado',
        'data_finalizacao',
        'anexo_id',
        'categoria_id',
        'user_id',
        'finalizer_user_id',
        'grupo_id',
        'template_id',
    ])
        ->setDescriptionForEvent(function(string $eventName) {
            $eventos = [
                'created' => 'criado',
                'updated' => 'atualizado',
                'deleted' => 'excluído',
            ];
            $eventoPt = $eventos[$eventName] ?? $eventName;
            return "Documento {$eventoPt}";
        });
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(Anexo::class, 'documento_id');
    }
}
