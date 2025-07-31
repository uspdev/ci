@extends('layouts.app')

@section('title', 'Documento: ' . $documento->codigo)

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <a href="{{ route('categoria.index') }}">Categorias</a> > <a
          href="{{ route('categoria.docs', $documento->categoria) }}"> {{ $documento->categoria->nome }} </a> >
        {{ $documento->codigo }}
      </h4>
      <div>
        <form action="{{ route('documento.copy', $documento) }}" method="POST" style="display:inline;">
          @csrf
          <button type="submit" class="btn btn-outline-success" title="Copiar documento">
            <i class="fas fa-copy"></i>
          </button>
        </form>
        @if (isset($documento->template))
          <a href="{{ route('documento.pdf', $documento) }}" class="btn btn-outline-secondary" target="_blank"
            title="Gerar documento">
            <i class="fas fa-file-pdf"></i>
          </a>
        @endif
        @unless ($documento->finalizado)
          <a href="{{ route('documento.edit', $documento) }}" class="btn btn-outline-primary" title="Editar documento">
            <i class="fas fa-edit"></i>
          </a>
          <form action="{{ route('documento.finalizar', $documento) }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-warning"
              title="Ao finalizar, este documento será marcado como concluído e não poderá mais ser editado. 
              Certifique-se de que todas as informações estão corretas antes de prosseguir."
              onclick="return confirm('Tem certeza que deseja finalizar este documento?')">
              <i class="fas fa-lock-open"></i> Finalizar
            </button>
          </form>
        @else
          <button type="button" class="btn btn btn-warning fs-6" disabled><i class="fas fa-lock"></i> Finalizado</button>
        @endunless
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-6">
          Código: <strong>{{ $documento->codigo }}</strong>
        </div>
        <div class="col-md-6 mt-2">
          Data do Documento: <strong>{{ $documento->data_documento->format('d/m/Y') }}</strong>
        </div>
        <div class="col-md-6 mt-2">
          Remetente: <strong>{{ $documento->remetente }}</strong>
        </div>
        <div class="col-md-6 mt-2">
          Destinatário: <strong>{{ $documento->destinatario }}</strong>
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
            @foreach ($documento->arquivos->sortByDesc('created_at') as $arquivo)
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
                    <i class="fas fa-download"></i>
                  </a>
                  <form action="{{ route('arquivo.destroy', $arquivo) }}" method="POST" style="display:inline;"
                    onsubmit="return confirm('Tem certeza que deseja excluir este arquivo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger ms-2">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
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
