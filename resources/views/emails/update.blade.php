@component('mail::message')
# Documento atualizado

Um documento foi **atualizado** no sistema.

**Dados do documento:**

- **Código:** {{ $documento->codigo ?? '-' }}
- **Categoria:** {{ $documento->categoria->nome ?? '-'}}
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