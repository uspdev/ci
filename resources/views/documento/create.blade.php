@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header">
      <h4 class="mb-0">{{ isset($documento) ? 'Editar documento: ' . $documento->codigo : 'Novo documento' }}</h4>
      @if (!isset($documento))
        <div class="mt-1">O código será gerado automaticamente</div>
      @endif
    </div>
    <div class="card-body">
    
      <form action="{{ isset($documento) ? route('documento.update', $documento) : route('documento.store') }}"
        method="POST">
        @csrf
        @if (isset($documento))
          @method('PUT')
        @endif

        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label for="categoria_id" class="form-label">Categoria</label>
              <select class="form-select" id="categoria_id" name="categoria_id" required>
                <option value="">Selecione a categoria</option>
                @foreach ($categorias as $categoria)
                  <option value="{{ $categoria->id }}"
                    {{ old('categoria_id', $documento->categoria_id ?? '') == $categoria->id ? 'selected' : '' }}>
                    {{ $categoria->nome }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

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
                    {{ old('template_id', $documento->template_id ?? '') == $template->id ? 'selected' : '' }}>
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
          <textarea class="form-control" id="mensagem" name="mensagem" rows="6" required>{{ old('mensagem', $documento->mensagem ?? '') }}</textarea>
        </div>

        @if (isset($documento) && $documento->anexos->count() > 0)
          <div class="mb-3">
            <h5>Anexos Existentes</h5>
            <div class="list-group">
              @foreach ($documento->anexos as $anexo)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ $anexo->nome_original }}</strong>
                    <small class="text-muted">({{ number_format($anexo->tamanho / 1024, 2) }} KB)</small>
                  </div>
                  <span class="badge bg-secondary">{{ $anexo->tipo_anexo }}</span>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success">
            {{ isset($documento) ? 'Atualizar' : 'Salvar' }}
          </button>
          @if (isset($documento))
            <a href="{{ route('documento.show', $documento) }}" class="btn btn-info ml-2">
              Visualizar
            </a>
          @endif
          <a href="{{ route('documento.index') }}" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Voltar
          </a>
        </div>
      </form>
    </div>
  </div>
@endsection
@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
  let editorInstance;
  
  ClassicEditor
    .create(document.querySelector('#mensagem'), {
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
    .then(editor => {
      editorInstance = editor;
            document.querySelector('form').addEventListener('submit', function(e) {
        document.querySelector('#mensagem').value = editor.getData();
      });
    })
    .catch(error => {
      console.error('CKEditor error: ', error);
    });
</script>
@endsection