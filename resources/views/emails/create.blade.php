@component('mail::message')
# Documento criado

Um novo documento foi **criado** na categoria {{ $documento->categoria->nome }}.

**Dados do documento:**

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
