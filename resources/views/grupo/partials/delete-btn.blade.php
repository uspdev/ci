<form action="{{ route('grupo.destroy', $grupo) }}" method="POST" style="display:inline;">
  @csrf
  @method('DELETE')
  <button type="submit" class="btn btn-sm btn-outline-danger py-0"
    onclick="return confirm('Tem certeza que deseja excluir este grupo?')">
    <i class="fas fa-trash"></i>
  </button>
</form>
