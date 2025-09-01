@extends('layouts.app')

@section('title', 'Documento: ' . $documento->codigo)

@section('content')
  <div class="card">
    <div class="card-header d-flex gap-2 flex-wrap card-header-sticky p-1">
      <div class="h4 ml-1">
        <a href="{{ route('categoria.show', $documento->categoria) }}"> {{ $documento->categoria->nome }} </a>
        <i class="fas fa-angle-right fa-sm"></i> {{ $documento->codigo }}
      </div>
      <div class="mr-2">
        @include('documento.partials.editar-btn')
        @include('documento.partials.pdf-btn')
        <span class="p-3"></span>
        @include('documento.partials.fazer-copia-btn')
        @include('documento.partials.finalizar-btn')
      </div>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-8" style="border-right: 1px solid #dee2e6; padding-right: 15px;">

          <div class="row mb-2">

            <div class="col-md-8">
              Documento
              <div class="border p-2 rounded bg-light">
                <strong>{{ $documento->codigo }}</strong>
              </div>
            </div>
            <div class="col-md-4">
              Data
              <div class="border p-2 rounded bg-light">
                <strong>{{ $documento->data_documento->format('d/m/Y') }}</strong>
              </div>
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-6 mt-2">
              De
              <div class="border p-2 rounded bg-light">
                <strong>{{ $documento->remetente }}</strong>
              </div>
            </div>
            <div class="col-md-6 mt-2">
              Para
              <div class="border p-2 rounded bg-light">
                <strong>{{ $documento->destinatario }}</strong>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <h5>Assunto</h5>
            <div class="border p-2 rounded bg-light">
              {{ $documento->assunto }}
            </div>
          </div>

          <div class="mb-4">
            <h5>Mensagem</h5>
            <div class="border p-2 rounded bg-light">
              {!! $documento->mensagem !!}
            </div>
          </div>

        </div>
        <div class="col-md-4">

          @if ($documento->arquivos->count() > 0)
            <div class="mb-4">
              <h5>Arquivos</h5>
              <div class="list-group">
                @foreach ($documento->arquivos->sortByDesc('created_at') as $arquivo)
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <a href="{{ route('arquivo.download', $arquivo) }}" target="_blank" class="font-weight-bold">
                        {{ $arquivo->nome_original }}
                      </a>
                      @include('documento.partials.arquivo-badge')
                      <br>
                      <small class="text-muted">
                        Tam.: {{ number_format($arquivo->tamanho / 1024, 2) }} KB |
                        Tipo: {{ $arquivo->tipo_mime }} |
                        Add em: {{ $arquivo->created_at->format('d/m/Y H:i') }}
                      </small>
                    </div>
                    <div>
                      @include('documento.partials.remover-arquivo-btn')
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          @include('documento.partials.arquivo-upload-btn')

        </div>
      </div>



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
