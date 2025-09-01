<form action="{{ route('arquivo.destroy', $arquivo) }}" 
method="POST" 
style="display:inline;"
  onsubmit="return confirm('Tem certeza que deseja excluir este arquivo?');">
  @csrf
  @method('DELETE')
  <button type="submit" class="btn btn-sm btn-outline-danger ms-2">
    <i class="fas fa-trash"></i>
  </button>
</form>
