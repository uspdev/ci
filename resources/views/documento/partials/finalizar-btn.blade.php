@if (!$documento->finalizado)
  <form action="{{ route('documento.finalizar', $documento) }}" method="POST" class="d-inline">
    @csrf
    @method('PATCH')
    <button type="submit" class="btn btn-sm btn-outline-warning"
      title="Ao finalizar, este documento será marcado como concluído e não poderá mais ser editado. 
              Certifique-se de que todas as informações estão corretas antes de prosseguir."
      onclick="return confirm('Ao finalizar, este documento será marcado como concluído e não poderá mais ser editado. Certifique-se de que todas as informações estão corretas antes de prosseguir. Tem certeza que deseja finalizar este documento?')">
      <i class="fas fa-lock-open"></i> Finalizar
    </button>
  </form>
@else
  <button type="button" class="btn btn btn-warning fs-6" disabled><i class="fas fa-lock"></i> Finalizado</button>
@endif
