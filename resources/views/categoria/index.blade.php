@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Categorias</h4>
    </div>
    <div class="card-body">
      @foreach ($categorias as $categoria)
        <div class="card mb-2">
          <div class="card-body h5"><a href="{{ route('categoria.docs', $categoria) }}">{{ $categoria->nome }}</a>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endsection