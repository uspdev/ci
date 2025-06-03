@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h4>Novo Template</h4>
    </div>
    <div class="card-body">
      <form action="{{ isset($template) ? route('template.update', $template) : route('template.store') }}"
        method="POST">
        @csrf
        @if (isset($template))
          @method('PUT')
        @endif

        <div class="mb-3">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" id="nome" name="nome"
            value="{{ old('nome', $template->nome ?? '') }}" required>
        </div>

        <div class="mb-3">
          <label for="descricao" class="form-label">Descrição</label>
          <textarea class="form-control" id="descricao" name="descricao" rows="2">{{ old('descricao', $template->descricao ?? '') }}</textarea>
        </div>

        <div class="mb-3">
          <label for="conteudo_padrao" class="form-label">Conteúdo Padrão</label>
          <textarea class="form-control" id="conteudo_padrao" name="conteudo_padrao" rows="6" required>{{ old('conteudo_padrao', $template->conteudo_padrao ?? '') }}</textarea>
        </div>

        <div class="mb-3">
          <label for="variaveis" class="form-label">Variáveis (JSON)</label>
          <textarea class="form-control" id="variaveis" name="variaveis" rows="2">{{ old('variaveis', isset($template) && is_array($template->variaveis) ? json_encode($template->variaveis) : '') }}</textarea>
          <small class="text-muted">Exemplo: {"nome":"string","data":"date"}</small>
        </div>

        <div class="mb-3">
          <label for="categorias" class="form-label">Categorias</label>
          <select class="form-select" id="categorias" name="categorias[]" multiple>
            @foreach ($categorias as $cat)
              <option value="{{ $cat->id }}"
                {{ (isset($template) && $template->categorias->contains($cat->id)) || collect(old('categorias'))->contains($cat->id) ? 'selected' : '' }}>
                {{ $cat->nome }}
              </option>
            @endforeach
          </select>
          <small class="text-muted">Selecione uma ou mais categorias.</small>
        </div>

        <button type="submit" class="btn btn-success">{{ isset($template) ? 'Atualizar' : 'Salvar' }}</button>
        <a href="{{ route('template.index') }}" class="btn btn-secondary">Voltar</a>
      </form>
    </div>
  </div>
@endsection
