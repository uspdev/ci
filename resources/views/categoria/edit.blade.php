@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h4 class="mb-0">Categorias
        <i class="fas fa-angle-right fa-sm"></i> {{ $categoria->nome }}
        <i class="fas fa-angle-right fa-sm"></i> Editar
      </h4>
    </div>
    <div class="card-body">
      <form action="{{ route('categoria.update', $categoria) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="next" value="{{ old('next', $next) }}">

        <div class="mb-3">
          <label for="nome" class="form-label">Nome da Categoria</label>
          <input type="text" class="form-control" id="nome" name="nome"
            value="{{ old('nome', $categoria->nome) }}" required>
        </div>
        <div class="mb-3">
          <label for="prefixo" class="form-label">Prefixo</label>
          <input type="text" class="form-control" id="prefixo" name="prefixo"
            value="{{ old('prefixo', $categoria->prefixo) }}" required>
          <small class="form-text text-muted">O prefixo será usado para gerar o código do documento.</small>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control" id="email" name="email"
              value="{{ old('email', $categoria->email) }}">
            <small class="form-text text-muted">Email para notificações relacionadas a esta categoria.</small>
          </div>
          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="controlar_sequencial" name="controlar_sequencial"
              value="1"
              {{ old('controlar_sequencial', $categoria->settings['controlar_sequencial']) ? 'checked' : '' }}>
            <label class="form-check-label" for="controlar_sequencial">
              Controlar Sequencial
            </label>
          </div>
          @if ($categoria->grupo->templates()->count() > 0)
            <div class="mb-3">
              <label class="form-label">Templates</label>
              <div>
                @foreach ($templates as $template)
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="template_{{ $template->id }}" name="templates[]"
                      value="{{ $template->id }}"
                      {{ (isset($categoria) && $categoria->templates->contains($template->id)) || collect(old('templates'))->contains($template->id) ? 'checked' : '' }}>
                    <label class="form-check-label" for="template_{{ $template->id }}">
                      {{ $template->nome }}
                    </label>
                  </div>
                @endforeach
              </div>
              <small class="text-muted">Selecione um ou mais templates.</small>
            </div>
          @endif
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
              Salvar
            </button>
            <a href="{{ $next }}" class="btn btn-secondary ml-2">
              <i class="fas fa-arrow-left"></i> Voltar
            </a>
          </div>
      </form>
    </div>
  </div>
@endsection
