<form id="formArquivo" action="{{ route('arquivo.upload', $documento) }}" method="POST" enctype="multipart/form-data">
  @csrf
  <label for="arquivos" class="form-label">Adicionar arquivo</label>
  <div class="input-group">
    <input type="file" class="form-control" id="arquivos" name="arquivo">
    <button class="btn btn-success ml-2 mb-2" type="submit">Salvar</button>
  </div>
</form>
