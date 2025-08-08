@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header" style="position: sticky; top: 0; z-index: 10;" >
      <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">{{ isset($documento) ? $documento->categoria->nome .' > '.  $documento->codigo . ' > Editar' : 'Novo documento' }}</h4>

        <div>
          <button type="submit" class="btn btn-success" form="formDocumento">
            Salvar
          </button>
          @if (isset($documento))
            <a href="{{ route('documento.show', $documento) }}" class="btn btn-warning ml-2">
              Cancelar
            </a>
          @endif
        </div>
      </div>
      @if (!isset($documento) && $categoria->settings['controlar_sequencial'] == 1)
        <div class="mt-1">O código será gerado automaticamente</div>
      @endif
    </div>
    <div class="card-body">

      <form id="formDocumento"
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
          <label for="arquivos" class="form-label">Adicionar Arquivo</label>
          <input type="file" class="form-control" id="arquivos" name="arquivo">
        </div>
        <button type="submit" class="btn btn-success" form="formDocumento">
          Salvar
        </button>
      </form>
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
