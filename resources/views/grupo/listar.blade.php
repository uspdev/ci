@extends('layouts.app')

@section('title', 'Grupos')

@section('content')
  @foreach ($grupos as $grupo)
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="h4 mb-0">Grupo {{ $grupo->name }}</div>
        <div>@include('grupo.partials.editar-btn', ['label' => 'Editar'])</div>
      </div>
      <div class="card-body">
        @foreach ($grupo->categorias as $categoria)
          <div class="card mb-2">
            <div class="card-body h5"><a href="{{ route('categoria.show', $categoria) }}">{{ $categoria->nome }}</a>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
@endsection
