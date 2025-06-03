@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Templates</h4>
      <a href="{{ route('template.create') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Novo Template
      </a>
    </div>
    <div class="card-body">
      @if ($templates->isEmpty())
        <div class="alert alert-info">Nenhum template cadastrado.</div>
      @else
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Descrição</th>
              <th>Categorias</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($templates as $template)
              <tr>
                <td>{{ $template->nome }}</td>
                <td>{{ Str::limit($template->descricao, 40) }}</td>
                <td>
                  @foreach ($template->categorias as $cat)
                    <span class="badge bg-secondary text-white">{{ $cat->nome }}</span>
                  @endforeach
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <a href="{{ route('template.show', $template) }}" class="btn btn-outline-success btn-sm mr-2 d-flex">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('template.gerarPdf', $template) }}"
                      class="btn btn-outline-secondary btn-sm mr-2 d-flex">
                      <i class="fas fa-file-pdf"></i>
                    </a>
                    <a href="{{ route('template.edit', $template) }}" class="btn btn-outline-primary btn-sm mr-2 d-flex">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('template.destroy', $template) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm d-flex"
                        onclick="return confirm('Tem certeza?')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
@endsection
