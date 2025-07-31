@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h4>Novo Template</h4>
    </div>
    <div class="card-body">
      <form action="{{ isset($template) ? route('template.update', $template) : route('template.store') }}"
        method="POST" enctype="multipart/form-data">
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

        <div class="mb-3">
          <label for="arquivo" class="form-label">Arquivo de template (.docx)</label>
          <input type="file" class="form-control" id="arquivo" name="arquivo" accept="application/docx"
            value="{{ old('arquivo', $template->arquivo ?? '') }}">
        </div>
        
        <button type="submit" class="btn btn-success">{{ isset($template) ? 'Atualizar' : 'Salvar' }}</button>
        <a href="{{ route('template.index') }}" class="btn btn-secondary">Voltar</a>
      </form>
    </div>
  </div>
@endsection

<style>
  .autoexpand {
    field-sizing: content;
    min-height: 100px
  }
</style>
{{--
Bloco para autoexpandir textarea conforme necessidade.

Uso:
- Incluir no layouts.app ou em outro lugar: @include('laravel-usp-theme::blocos.textarea-autoexpand')
- Adiconar a classe 'autoexpand'

@author Masakik, em 8/5/2024
--}}

@once
  @section('javascripts_bottom')
    @parent
    <script>
      $(document).ready(function() {
        // valida fields antes de submeter o formulário
        $('#form-definition-form').on('submit', function(e) {
          const jsonText = $('#fields').val()

          try {
            JSON.parse(jsonText)
          } catch (error) {
            e.preventDefault();
            alert('O JSON precisa ser válido!')
          }
        })

      })
    </script>
  @endsection
@endonce