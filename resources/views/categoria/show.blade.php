@extends('layouts.app')

@section('title', 'Categoria: ' . $categoria->nome)

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Categoria: {{ $categoria->nome }}</h4>
      <div>
        <a href="{{ route('categoria.edit', $categoria) }}" class="btn btn-primary">
          <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('categoria.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Voltar
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-6">
          <strong>Nome:</strong> {{ $categoria->nome }} - {{ $categoria->abreviacao }}
        </div>
        <div class="col-md-6">
          <strong>Setor:</strong> {{ $categoria->setor->name }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Criado em:</strong> {{ $categoria->created_at->format('d/m/Y H:i') }}
        </div>
        <div class="col-md-6 mt-2">
          <strong>Atualizado em:</strong> {{ $categoria->updated_at->format('d/m/Y H:i') }}
        </div>
      </div>
      <div class="mb-4">
        <h5>Templates Associados</h5>
        @if ($categoria->templates->count() > 0)

          <div class="list-group">
            @foreach ($categoria->templates as $template)
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $template->nome }}</strong>
                  @if ($template->descricao)
                    <br>
                    <small class="text-muted">{{ Str::limit($template->descricao, 100) }}</small>
                  @endif
                </div>
                <span class="badge bg-info">Template</span>
              </div>
            @endforeach
          </div>
        @else
          <div class="alert alert-info">
            Nenhum template associado a esta categoria.
          </div>
        @endif
      </div>
      <div class="mb-4">
        <h5>Documentos desta Categoria</h5>
        @if ($categoria->documentos->count() > 0)

          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Tipo</th>
                  <th>Assunto</th>
                  <th>Status</th>
                  <th>Data</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($categoria->documentos->take(10) as $documento)
                  <tr>
                    <td>{{ $documento->codigo }}</td>
                    <td>
                      <span class="badge bg-secondary text-white">{{ ucfirst($documento->tipo) }}</span>
                    </td>
                    <td>{{ Str::limit($documento->assunto, 40) }}</td>
                    <td>
                      @if ($documento->finalizado)
                        <span class="badge bg-success text-white">Finalizado</span>
                      @else
                        <span class="badge bg-warning">Em andamento</span>
                      @endif
                    </td>
                    <td>{{ $documento->data_documento->format('d/m/Y') }}</td>
                    <td>
                      <a href="{{ route('documento.show', $documento) }}" class="btn btn-sm btn-info">
                        Visualizar
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>

            @if ($categoria->documentos->count() > 10)
              <div class="text-center mt-3">
                <small class="text-muted">
                  Mostrando 10 de {{ $categoria->documentos->count() }} documentos.
                  <a href="{{ route('documento.index') }}?categoria_id={{ $categoria->id }}"
                    class="btn btn-sm btn-outline-primary ms-2">
                    Ver todos os documentos
                  </a>
                </small>
              </div>
            @endif
          </div>
        @else
          <div class="alert alert-info">
            Nenhum documento criado nesta categoria ainda.
            <a href="{{ route('documento.create') }}">
              Criar primeiro documento
            </a>
          </div>
        @endif
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-body text-center">
              <h5 class="card-title">{{ $categoria->documentos->count() }}</h5>
              <p class="card-text">Total de Documentos</p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-body text-center">
              <h5 class="card-title">{{ $categoria->documentos->where('finalizado', true)->count() }}</h5>
              <p class="card-text">Documentos Finalizados</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
