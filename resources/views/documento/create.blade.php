@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header card-header-sticky d-flex gap-2 flex-wrap p-1">
      <div class="h4 ml-2 mb-0">
        @if (isset($documento))
          {{ $documento->categoria->nome }}
          <i class="fas fa-angle-right"></i> {{ $documento->codigo }}
          <i class="fas fa-angle-right"></i> Editar
        @else
          {{ $categoria->nome }}
          <i class="fas fa-angle-right"></i> Novo documento
          @if (!isset($documento) && $categoria->settings['controlar_sequencial'] == 1)
            <div class="small text-muted mt-1">O código será gerado automaticamente</div>
          @endif
        @endif
      </div>
      <div class="flex">
        <button type="submit" class="btn btn-sm btn-outline-success" form="formDocumento">
          <i class="fas fa-save"></i> Salvar
        </button>
        @if (isset($documento))
          <a href="{{ route('documento.show', $documento) }}" class="btn btn-sm btn-outline-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Voltar
          </a>
        @else
          <a href="{{ route('categoria.show', $categoria) }}" class="btn btn-sm btn-outline-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Voltar
          </a>
        @endif


      </div>
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
          <div class="col-md-8">
            <div class="mb-3">
              <label for="destinatario" class="form-label">Destinatário</label>
              <input type="text" class="form-control" id="destinatario" name="destinatario"
                value="{{ old('destinatario', $documento->destinatario ?? '') }}" required>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label for="data_documento" class="form-label">Data do Documento</label>
              <input type="date" class="form-control" id="data_documento" name="data_documento"
                value="{{ old('data_documento', isset($documento) ? $documento->data_documento->format('Y-m-d') : '') }}"
                required>
            </div>

          </div>
        </div>

        <div class="row">
          <div class="col-md-8">
            <div class="mb-3">
              <label for="remetente" class="form-label">Remetente</label>
              <input type="text" class="form-control" id="remetente" name="remetente"
                value="{{ old('remetente', $documento->remetente ?? '') }}" required>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label for="template_id" class="form-label">Template</label>
              <select class="form-control" id="template_id" name="template_id">
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
          <input class="form-control" id="assunto" name="assunto" rows="3" required
            value="{{ old('assunto', $documento->assunto ?? '') }}">
        </div>




        <div class="mb-3">
          <label for="mensagem" class="form-label">Mensagem</label>
          <textarea id="mensagem" name="mensagem">{{ old('mensagem', $documento->mensagem ?? '') }}</textarea>
        </div>



        <button type="submit" class="btn btn-outline-success" form="formDocumento">
          Salvar
        </button>
      </form>
    </div>
  </div>
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endsection

@section('javascripts_bottom')
  @parent

  {{-- TinyMCE CDN --}}
  <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      tinymce.init({
        selector: '#mensagem',
        height: 300,
        plugins: 'table lists link image', // ⚡ tabela incluída aqui
        toolbar: 'undo redo | bold italic underline | bullist numlist | link image | table | fontsizeselect', // ⚡ botão table separado
        menubar: 'file edit view insert format tools table help', // para menu completo (opcional)
        fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        branding: false, // 🔹 remove "Powered by TinyMCE"
        promotion: false // 🔹 remove botão de upgrade / propaganda
      });
    });
  </script>
@endsection
