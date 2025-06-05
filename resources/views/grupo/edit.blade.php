@extends('layouts.app')

@section('content')
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header h4">
          <a href="{{ route('grupo.index') }}">Grupos</a>
          <i class="fas fa-angle-right"></i>
          {{ $grupo->name }}
        </div>

        <div class="card-body">
          <form action="{{ route('grupo.update', $grupo->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
              <label for="name">Nome do grupo</label>
              <input type="text" id="name" name="name" class="form-control" value="{{ $grupo->name }}"
                required>
            </div>

            <div class="form-group">
              <label for="description">Descrição</label>
              <textarea id="description" name="description" class="form-control" rows="3">{{ $grupo->description }}</textarea>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary" disabled>Salvar Alterações</button>
              <a href="{{ route('grupo.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <form method="post" id="form-{{ $grupo->id }}"
          action="{{ route('grupo.editarResponsavel', ['id' => $grupo->id]) }}">
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
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const nameInput = document.getElementById('name');
      const descInput = document.getElementById('description');
      const submitBtn = document.querySelector('button[type="submit"]');

      const originalValues = {
        name: nameInput.value,
        description: descInput.value
      };

      function checkChanges() {
        const nameChanged = nameInput.value !== originalValues.name;
        const descChanged = descInput.value !== originalValues.description;
        submitBtn.disabled = !(nameChanged || descChanged);
      }

      nameInput.addEventListener('input', checkChanges);
      descInput.addEventListener('input', checkChanges);
    });
  </script>
@endsection
