@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h4 class="mb-0">{{ isset($documento) ? 'Editar documento: ' . $documento->codigo : 'Novo documento' }}</h4>
      @if (!isset($documento) && $categoria->settings['controlar_sequencial'] == 1)
        <div class="mt-1">O código será gerado automaticamente</div>
      @endif
    </div>
    <div class="card-body">

      <form
        action="{{ isset($documento) ? route('documento.update', $documento) : route('categoria.store.doc', $categoria) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @if (isset($documento))
          @method('PUT')
        @endif

        @if ($categoria->settings['controlar_sequencial'] != 1)
          <div class="mb-3">
            <label for="codigo" class="form-label">Código</label>
            <input type="text" class="form-control" id="codigo" name="codigo"
              value="{{ old('codigo', $documento->codigo ?? '') }}" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="ano" class="form-label">Ano</label>
                <input type="number" class="form-control" id="ano" name="ano"
                  value="{{ old('ano', $documento->ano ?? '') }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="sequencial" class="form-label">Sequencial</label>
                <input type="number" class="form-control" id="sequencial" name="sequencial"
                  value="{{ old('sequencial', $documento->sequencial ?? '') }}" required>
              </div>
            </div>
          </div>
        @endif

        @php
          if (isset($documento)) {
              $prefixo = \Illuminate\Support\Str::beforeLast($documento->codigo, ' Nº');
          }
        @endphp
        @if ($categoria->settings['controlar_sequencial'] == 1)
          <div class="mb-3">
            <label for="prefixo" class="form-label">Prefixo</label>
            <input type="text" class="form-control" id="prefixo" name="prefixo"
              value="{{ old('prefixo', $prefixo ?? '') }}" required>
          </div>
        @endif
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label for="destinatario" class="form-label">Destinatário</label>
              <input type="text" class="form-control" id="destinatario" name="destinatario"
                value="{{ old('destinatario', $documento->destinatario ?? '') }}" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label for="remetente" class="form-label">Remetente</label>
              <input type="text" class="form-control" id="remetente" name="remetente"
                value="{{ old('remetente', $documento->remetente ?? '') }}" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label for="data_documento" class="form-label">Data do Documento</label>
              <input type="date" class="form-control" id="data_documento" name="data_documento"
                value="{{ old('data_documento', isset($documento) ? $documento->data_documento->format('Y-m-d') : '') }}"
                required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label for="template_id" class="form-label">Template</label>
              <select class="form-select" id="template_id" name="template_id">
                <option value="">Nenhum template</option>
                @foreach ($templates as $template)
                  <option value="{{ $template->id }}"
                    {{ old('template_id', $documento->template_id ?? '') == $template->id ||
                    (count($templates) === 1 && empty(old('template_id', $documento->template_id ?? '')))
                        ? 'selected'
                        : '' }}>
                    {{ $template->nome }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label for="assunto" class="form-label">Assunto</label>
          <textarea class="form-control" id="assunto" name="assunto" rows="3" required>{{ old('assunto', $documento->assunto ?? '') }}</textarea>
        </div>

        <div class="mb-3">
          <label for="mensagem" class="form-label">Mensagem</label>
          <textarea class="form-control" id="mensagem" name="mensagem" rows="6">{{ old('mensagem', $documento->mensagem ?? '') }}</textarea>
        </div>

        <div class="mb-3">
          <label for="arquivos" class="form-label">Adicionar Arquivos</label>
          <input type="file" class="form-control" id="arquivos" name="arquivos[]" multiple>
          <small class="form-text text-muted">Você pode selecionar mais de um arquivo.</small>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success">
            {{ isset($documento) ? 'Atualizar' : 'Salvar' }}
          </button>
          @if (isset($documento))
            <a href="{{ route('documento.show', $documento) }}" class="btn btn-info ml-2">
              Visualizar
            </a>
          @endif
          <a href="{{ route('categoria.docs', $categoria) }}" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Voltar
          </a>
        </div>
      </form>

      @if (isset($documento) && $documento->arquivos->count() > 0)
        <div class="mt-3">
          <h5>Arquivos Existentes</h5>
          <div class="list-group">
            @foreach ($documento->arquivos as $arquivo)
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $arquivo->nome_original }}</strong>
                  <small class="text-muted">({{ number_format($arquivo->tamanho / 1024, 2) }} KB)</small>
                </div>
                <div>
                  <span class="badge bg-secondary text-white">{{ $arquivo->tipo_arquivo }}</span>
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
    </div>
  </div>
@endsection
@section('javascripts_bottom')
  <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var mensagemField = document.querySelector('#mensagem');
      if (mensagemField) {
        ClassicEditor
          .create(mensagemField, {
            toolbar: {
              items: [
                'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList',
                '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'undo', 'redo'
              ]
            },
            language: 'pt-br',
            table: {
              contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
            }
          })
          .catch(error => {
            console.error('CKEditor error: ', error);
          });
      } else {
        console.error('Campo #mensagem não encontrado no DOM.');
      }
    });
  </script>
@endsection
