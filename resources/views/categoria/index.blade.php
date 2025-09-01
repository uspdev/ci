@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center card-header-sticky">
      <h4 class="mb-0">Categorias</h4>
      @include('categoria.partials.novo-btn')
    </div>
    <div class="card-body">
      @foreach ($categorias as $categoria)
        <div class="card mb-2">
          <div class="card-body d-flex justify-content-between align-items-center">
            
            <a href="{{ route('categoria.show', $categoria) }}" class="h4">{{ $categoria->nome }}</a>
            
            <div class="btn-group ml-auto gap-2" role="group">
              @include('categoria.partials.editar-btn')

              @can('admin')
                <form action="{{ route('categoria.destroy', $categoria) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger"
                    onclick="return confirm('Tem certeza?')">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              @endcan
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endsection
