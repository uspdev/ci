@if (!$documento->finalizado)
  <a href="{{ route('documento.edit', $documento) }}" class="btn btn-sm btn-outline-primary" title="Editar documento">
    <i class="fas fa-edit"></i> Editar
  </a>
@endif
