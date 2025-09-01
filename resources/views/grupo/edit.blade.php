@extends('layouts.app')

@section('content')
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header h4">
          <a href="{{ route('grupo.index') }}">Grupos</a>
          <i class="fas fa-angle-right"></i> {{ $grupo->name }}
        </div>

        <div class="card-body">
          <form action="{{ route('grupo.update', $grupo->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="next" value="{{ $next }}">

            <div class="form-group">
              <label for="name">Nome do grupo</label>
              <input type="text" id="name" name="name" class="form-control" value="{{ $grupo->name }}"
                required>
            </div>

            <div class="form-group">
              <label for="description">Descrição</label>
              <textarea id="description" name="description" class="form-control" rows="3">{{ $grupo->description }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Templates</label>
              <div>
                @foreach ($templates as $template)
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="template_{{ $template->id }}" name="templates[]"
                      value="{{ $template->id }}"
                      {{ (isset($grupo) && $grupo->templates->contains($template->id)) || collect(old('templates'))->contains($template->id) ? 'checked' : '' }}>
                    <label class="form-check-label" for="template_{{ $template->id }}">
                      {{ $template->nome }}
                    </label>
                  </div>
                @endforeach
              </div>
              <small class="text-muted">Selecione um ou mais templates.</small>
            </div>
            
            <div class="form-group">
              <button type="submit" class="btn btn-primary">Salvar Alterações</button>
              <a href="{{ route('grupo.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
            
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      @include('grupo.partials.pessoas-card')
    </div>
  </div>
@endsection
