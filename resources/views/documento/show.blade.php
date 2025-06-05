@extends('layouts.app')

@section('title', 'Documento: ' . $documento->codigo)

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Documento: {{ $documento->codigo }}</h4>
      <div>
        @unless ($documento->finalizado)
          <a href="{{ route('documento.edit', $documento) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
          </a>
          <form action="{{ route('documento.finalizar', $documento) }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-warning"
              onclick="return confirm('Tem certeza que deseja finalizar este documento?')">
              <i class="fas fa-check"></i> Finalizar
            </button>
          </form>
        @else
          <span class="badge bg-success fs-6 text-white">Documento Finalizado</span>
        @endunless
        @if (isset($documento->template))
            <a href="{{ route('documento.pdf', $documento->id) }}" class="btn btn-outline-secondary" target="_blank">
              <i class="fas fa-file-pdf"></i> Gerar PDF
            </a>
        @endif
        <a href="{{ route('documento.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Voltar
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-6">
          <strong>Código:</strong> {{ $documento->codigo }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Destinatário:</strong> {{ $documento->destinatario }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Remetente:</strong> {{ $documento->remetente }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Data do Documento:</strong> {{ $documento->data_documento->format('d/m/Y') }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Categoria:</strong> {{ $documento->categoria->nome }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Grupo:</strong> {{ $documento->categoria->grupo->name }}
        </div>
        @if ($documento->template)
          <div class="col-md-6 mt-2">
            <strong>Template:</strong> {{ $documento->template->nome }}
          </div>
        @endif
        <div class="col-md-6 mt-2">
          <strong>Status:</strong>
          @if ($documento->finalizado)
            Finalizado por {{ \Uspdev\Replicado\Pessoa::nomeCompleto(\App\Models\User::find($documento->finalizer_user_id)->codpes) }} em {{ $documento->data_finalizacao->format('d/m/Y H:i') }}
          @else
            <span class="badge bg-warning">Em andamento</span>
          @endif
        </div>
      </div>

      <div class="mb-4">
        <h5>Assunto</h5>
        <div class="border p-3 rounded bg-light">
          {{ $documento->assunto }}
        </div>
      </div>

      <div class="mb-4">
        <h5>Mensagem</h5>
        <div class="border p-3 rounded bg-light">
          {!! $documento->mensagem !!}
        </div>
      </div>

      @if ($documento->anexos->count() > 0)
        <div class="mb-4">
          <h5>Anexos</h5>
          <div class="list-group">
            @foreach ($documento->anexos as $anexo)
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $anexo->nome_original }}</strong>
                  <br>
                  <small class="text-muted">
                    Tamanho: {{ number_format($anexo->tamanho / 1024, 2) }} KB |
                    Tipo: {{ $anexo->tipo_mime }} |
                    Adicionado em: {{ $anexo->created_at->format('d/m/Y H:i') }}
                  </small>
                </div>
                <div>
                  <span class="badge bg-secondary me-2 text-white">{{ ucfirst($anexo->tipo_anexo) }}</span>
                  <a href="{{ Storage::url($anexo->caminho) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download"></i> Download
                  </a>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="row mt-4 pt-3 border-top">
        <div class="col-md-6">
          <small class="text-muted">
            <i class="fas fa-plus-circle"></i> <strong>Criado em:</strong>
            {{ $documento->created_at->format('d/m/Y H:i') }}
          </small>
        </div>
        <div class="col-md-6">
          <small class="text-muted">
            <i class="fas fa-edit"></i> <strong>Atualizado em:</strong> {{ $documento->updated_at->format('d/m/Y H:i') }}
          </small>
        </div>
      </div>
    </div>
  </div>

  @if (isset($activities))
    <div class="card activities mt-3">
      <div class="card-header h5">Atividades</div>
      <div class="card-body">
        @foreach ($activities as $activity)
          {{ $activity->created_at->format('d/m/Y H:i:s') }} |
          <strong> <a href="{{ route('documento.atividade', $activity->id) }}">
              {{ $activity->description }}</a>
          </strong>
          por {{ \App\Models\User::findOrFail($activity->causer_id)->name }}
          em {{ $activity->properties['agent']['ip'] }}
          <br>
        @endforeach
      </div>
    </div>
  @endif
@endsection
