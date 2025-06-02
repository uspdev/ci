@extends('layouts.app')

@section('content')
  <div class="container">
    <h3>Detalhes da Atividade - Documento</h3>
    <p>
      <strong>Data:</strong> {{ $activity->created_at->format('d/m/Y H:i:s') }}<br>
      <strong>Descrição:</strong> {{ $activity->description }}<br>
      <strong>Usuário:</strong> {{ \App\Models\User::findOrFail($activity->causer_id)->name }}<br>
      <strong>IP:</strong> {{ $activity->properties['agent']['ip'] ?? '-' }}
    </p>
    <hr>
    <h4>Alterações no Documento</h4>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Campo</th>
          <th>Antes</th>
          <th>Depois</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Código</td>
          <td>{{ $old['codigo'] ?? '-' }}</td>
          <td>{{ $new['codigo'] ?? '-' }}</td>
        </tr>
        <tr>
          <td>Destinatário</td>
          <td>{{ $old['destinatario'] ?? '-' }}</td>
          <td>{{ $new['destinatario'] ?? '-' }}</td>
        </tr>
        <tr>
          <td>Remetente</td>
          <td>{{ $old['remetente'] ?? '-' }}</td>
          <td>{{ $new['remetente'] ?? '-' }}</td>
        </tr>
        <tr>
          <td>Data do Documento</td>
          <td>
            @if (!empty($old['data_documento']))
              {{ \Carbon\Carbon::parse($old['data_documento'])->format('d/m/Y') }}
            @else
              -
            @endif
          </td>
          <td>
            @if (!empty($new['data_documento']))
              {{ \Carbon\Carbon::parse($new['data_documento'])->format('d/m/Y') }}
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <td>Assunto</td>
          <td>{{ Str::limit($old['assunto'] ?? '-', 100) }}</td>
          <td>{{ Str::limit($new['assunto'] ?? '-', 100) }}</td>
        </tr>
        <tr>
          <td>Mensagem</td>
          <td>
            @if (!empty($old['mensagem']))
              <div style="max-height: 100px; overflow-y: auto;">
                {!! Str::limit(strip_tags($old['mensagem']), 200) !!}
              </div>
            @else
              -
            @endif
          </td>
          <td>
            @if (!empty($new['mensagem']))
              <div style="max-height: 100px; overflow-y: auto;">
                {!! Str::limit(strip_tags($new['mensagem']), 200) !!}
              </div>
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <td>Status</td>
          <td>
            @if (isset($old['finalizado']))
              @if ($old['finalizado'])
                Finalizado
              @else
                Em andamento
              @endif
            @else
              -
            @endif
          </td>
          <td>
            @if (isset($new['finalizado']))
              @if ($new['finalizado'])
                Finalizado
              @else
                Em andamento
              @endif
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <td>Data Finalização</td>
          <td>
            @if (!empty($old['data_finalizacao']))
              {{ \Carbon\Carbon::parse($old['data_finalizacao'])->format('d/m/Y H:i') }}
            @else
              -
            @endif
          </td>
          <td>
            @if (!empty($new['data_finalizacao']))
              {{ \Carbon\Carbon::parse($new['data_finalizacao'])->format('d/m/Y H:i') }}
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <td>Categoria</td>
          <td>
            @if (!empty($old['categoria_id']))
              {{ \App\Models\Categoria::find($old['categoria_id'])->nome ?? 'Categoria não encontrada' }}
            @else
              -
            @endif
          </td>
          <td>
            @if (!empty($new['categoria_id']))
              {{ \App\Models\Categoria::find($new['categoria_id'])->nome ?? 'Categoria não encontrada' }}
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <td>Template</td>
          <td>
            {{-- @if (!empty($old['template_id']))
              {{ \App\Models\Template::find($old['template_id'])->nome ?? 'Template não encontrado' }}
            @else
              Nenhum template
            @endif --}}
          </td>
          <td>
            {{-- @if (!empty($new['template_id']))
              {{ \App\Models\Template::find($new['template_id'])->nome ?? 'Template não encontrado' }}
            @else
              Nenhum template
            @endif --}}
          </td>
        </tr>
        <tr>
          <td>Setor</td>
          <td>
            @if (!empty($old['setor_id']))
              {{ \App\Models\Setor::find($old['setor_id'])->name ?? 'Setor não encontrado' }}
            @else
              -
            @endif
          </td>
          <td>
            @if (!empty($new['setor_id']))
              {{ \App\Models\Setor::find($new['setor_id'])->name ?? 'Setor não encontrado' }}
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <td>Usuário Criador</td>
          <td>
            @if (!empty($old['user_id']))
              {{ \App\Models\User::find($old['user_id'])->name ?? 'Usuário não encontrado' }}
            @else
              -
            @endif
          </td>
          <td>
            @if (!empty($new['user_id']))
              {{ \App\Models\User::find($new['user_id'])->name ?? 'Usuário não encontrado' }}
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <td>Usuário Finalizador</td>
          <td>
            @if (!empty($old['finalizer_user_id']))
              {{ \App\Models\User::find($old['finalizer_user_id'])->name ?? 'Usuário não encontrado' }}
            @else
              -
            @endif
          </td>
          <td>
            @if (!empty($new['finalizer_user_id']))
              {{ \App\Models\User::find($new['finalizer_user_id'])->name ?? 'Usuário não encontrado' }}
            @else
              -
            @endif
          </td>
        </tr>
      </tbody>
    </table>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Voltar</a>
  </div>
@endsection
