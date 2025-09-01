<div class="card h-100">
  <form method="post" id="form-{{ $grupo->id }}" action="{{ route('grupo.editarResponsavel', $grupo) }}">
    @csrf
    @method('put')
    <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">

    <div class="card-header h5 py-2">
      Pessoas
      @include('partials.codpes-adicionar-btn')
      <div class="small text-secondary">Pessoas autorizadas a gerenciar o grupo</div>
    </div>

    <div class="card-body py-1">
      @foreach ($grupo->users() as $admin)
        <div class="hover-effect">
          {{ $admin->name }}
          @include('partials.codpes-remover-btn', ['codpes' => $admin->codpes])
        </div>
      @endforeach
    </div>
  </form>
</div>
