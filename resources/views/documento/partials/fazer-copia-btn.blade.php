<form action="{{ route('documento.copy', $documento) }}" method="POST" style="display:inline;">
  @csrf
  <button type="submit" class="btn btn-sm btn-outline-success" title="Cria novo documento com os mesmos dados deste!">
    <i class="fas fa-copy"></i> Fazer cópia
  </button>
</form>
