@component('mail::message')
# Documento atualizado

Um documento foi **atualizado** na categoria {{ $documento->categoria->nome }}.

**Dados do documento original:**

- **Código:** {{ $original->codigo ?? '-' }}
- **Autor:** {{ \App\Models\User::find($original->user_id)->name ?? '-'}}
- **Grupo:** {{ $original->categoria->grupo->name ?? '-' }}
- **Data:** {{ $original->data_documento ?? '-' }}
- **Destinatário:** {{ $original->destinatario ?? '-' }}
- **Remetente:** {{ $original->remetente ?? '-' }}
- **Assunto:** {{ $original->assunto ?? '-' }}

**Dados do documento atualizado:**

- **Código:** {{ $documento->codigo ?? '-' }}
- **Autor:** {{ \App\Models\User::find($documento->user_id)->name ?? '-'}}
- **Grupo:** {{ $documento->categoria->grupo->name ?? '-' }}
- **Data:** {{ $documento->data_documento ?? '-' }}
- **Destinatário:** {{ $documento->destinatario ?? '-' }}
- **Remetente:** {{ $documento->remetente ?? '-' }}
- **Assunto:** {{ $documento->assunto ?? '-' }}

@component('mail::button', ['url' => config('app.url')])
Acessar o sistema
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent