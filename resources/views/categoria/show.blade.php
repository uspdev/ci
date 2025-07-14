@extends('layouts.app')

@section('title', 'Categoria: ' . $categoria->nome)

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Categoria: {{ $categoria->nome }}</h4>
      <div>
        <a href="{{ route('categoria.edit', $categoria) }}" class="btn btn-outline-primary">
          <i class="fas fa-edit"></i> 
        </a>
        <a href="{{ route('categoria.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Voltar
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-6">
          <strong>Nome:</strong> {{ $categoria->nome }} - {{ $categoria->prefixo }}
        </div>
        <div class="col-md-6">
          <strong>Grupo:</strong> {{ $categoria->grupo->name }}
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
              </div>
            @endforeach
          </div>
        @else
          <div class="alert alert-info">
            Nenhum template associado a esta categoria.
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
