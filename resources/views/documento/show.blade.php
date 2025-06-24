@extends('layouts.app')

@section('title', 'Documento: ' . $documento->codigo)

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Documento: {{ $documento->codigo }}</h4>
      <div>
        <form action="{{ route('documento.copy', $documento->id) }}" method="POST" style="display:inline;">
          @csrf
          <button type="submit" class="btn btn-outline-success ml-2">
            <i class="fas fa-copy"></i>
          </button>
        </form>
        @if (isset($documento->template))
          <a href="{{ route('documento.pdf', $documento->id) }}" class="btn btn-outline-secondary" target="_blank">
            <i class="fas fa-file-pdf"></i>
          </a>
        @endif
        @unless ($documento->finalizado)
          <a href="{{ route('documento.edit', ['categoria' => $documento->categoria_id, 'id' => $documento]) }}"
            class="btn btn-outline-primary">
            <i class="fas fa-edit"></i>
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
        <a href="{{ route('documento.index', ['categoria' => $documento->categoria_id]) }}" class="btn btn-secondary">
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
          <strong>Data do Documento:</strong> {{ $documento->data_documento->format('d/m/Y') }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Destinatário:</strong> {{ $documento->destinatario }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Remetente:</strong> {{ $documento->remetente }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Categoria:</strong> {{ $documento->categoria->nome }}
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

      @if ($documento->arquivos->count() > 0)
        <div class="mb-4">
          <h5>Arquivos</h5>
          <div class="list-group">
            @foreach ($documento->arquivos as $arquivo)
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $arquivo->nome_original }}</strong>
                  <br>
                  <small class="text-muted">
                    Tamanho: {{ number_format($arquivo->tamanho / 1024, 2) }} KB |
                    Tipo: {{ $arquivo->tipo_mime }} |
                    Adicionado em: {{ $arquivo->created_at->format('d/m/Y H:i') }}
                  </small>
                </div>
                <div>
                  <span class="badge bg-secondary me-2 text-white">{{ ucfirst($arquivo->tipo_arquivo) }}</span>
                  <a href=".{{ Storage::url($arquivo->caminho) }}" target="_blank"
                    class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download"></i> Download
                  </a>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="mt-4 pt-3 border-top">
        <small class="text-muted d-flex align-items-center flex-wrap">
          <span class="mr-4">
            <i class="fas fa-plus-circle"></i>
            <strong>Criado em:</strong> {{ $documento->created_at->format('d/m/Y H:i') }}
          </span>
          <span class="mr-4">
            <i class="fas fa-edit"></i>
            <strong>Atualizado em:</strong> {{ $documento->updated_at->format('d/m/Y H:i') }}
          </span>
          <span class="mr-4">
            <strong>Grupo:</strong> {{ $documento->categoria->grupo->name }}
          </span>
          <span class="mr-4">
            <strong>Status:</strong>
            @if ($documento->finalizado)
              Finalizado por
              {{ \Uspdev\Replicado\Pessoa::nomeCompleto(\App\Models\User::find($documento->finalizer_user_id)->codpes) }}
              em {{ $documento->data_finalizacao->format('d/m/Y H:i') }}
            @else
              Em andamento
            @endif
          </span>
          @if ($documento->template)
            <span class="mr-4">
              <strong>Template:</strong> {{ $documento->template->nome }}
            </span>
          @endif
        </small>
      </div>
    </div>
  </div>

  @if (isset($activities))
    @include('documento.partials.atividades-card')
  @endif
@endsection
