@extends('laravel-usp-theme::master')

@section('content')
  <div class="card">
    <div class="card-header h4">
      <a href="{{ route('grupo.index') }}">Grupos</a>
      <i class="fas fa-angle-right"></i>
      Criar grupo
    </div>
    <div class="card-body">
      <form action="{{ route('grupo.store') }}" method="POST">
        @csrf
        <div class="form-group">
          <label for="name">Nome do grupo</label>
          <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
          <label for="description">Descrição</label>
          <input type="text" id="description" name="description" class="form-control" value="{{ old('description') }}">
        </div>
        <button type="submit" class="btn btn-primary mt-3">Salvar</button>
        <a href="{{ route('grupo.index') }}" class="btn btn-secondary btn-sm mr-2 mt-3 pb-2">
          Cancelar
        </a>
      </form>
    </div>
  </div>
@endsection
