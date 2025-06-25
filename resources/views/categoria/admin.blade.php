@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Categorias</h4>
      <a href="{{ route('categoria.create') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Nova Categoria
      </a>
    </div>
    <div class="card-body">
      <table id="categorias-table" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($categorias as $categoria)
            <tr>
              <td>{{ $categoria->id }}</td>
              <td>{{ $categoria->nome }}</td>
              <td>{{ $categoria->created_at->format('d/m/Y H:i') }}</td>
              <td>
                <div class="btn-group" role="group">
                  {{-- <a href="{{ route('categoria.show', $categoria) }}" class="btn btn-outline-success btn-sm mr-2 d-flex">
                    <i class="fas fa-eye"></i>
                  </a> --}}
                  <a href="{{ route('categoria.edit', $categoria) }}" class="btn btn-outline-primary btn-sm mr-2 d-flex">
                    <i class="fas fa-edit"></i>
                  </a>
                  @can('admin')
                    <form action="{{ route('categoria.destroy', $categoria) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm d-flex" onclick="return confirm('Tem certeza?')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
