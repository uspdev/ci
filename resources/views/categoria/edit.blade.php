@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h4 class="mb-0">Editar Categoria: {{ $categoria->nome }}</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('categoria.update', $categoria) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label for="nome" class="form-label">Nome da Categoria</label>
          <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome"
            value="{{ old('nome', $categoria->nome) }}" required>
          @error('nome')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success">
            Atualizar
          </button>
          <a href="{{ route('categoria.index') }}" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Voltar
          </a>
        </div>
      </form>
    </div>
  </div>
@endsection
