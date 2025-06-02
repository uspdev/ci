@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Documentos</h4>
      <a href="{{ route('documento.create') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Novo Documento
      </a>
    </div>
    <div class="card-body">
      <table id="documentos-table" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Código</th>
            <th>Destinatário</th>
            <th>Assunto</th>
            <th>Categoria</th>
            <th>Status</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($documentos as $documento)
            <tr>
              <td>{{ $documento->codigo }}</td>
              <td>{{ $documento->destinatario }}</td>
              <td>{{ Str::limit($documento->assunto, 30) }}</td>
              <td>{{ $documento->categoria->nome }}</td>
              <td>
                @if ($documento->finalizado)
                  <span class="badge bg-success text-white">Finalizado</span>
                @else
                  <span class="badge bg-warning">Em andamento</span>
                @endif
              </td>
              <td>{{ $documento->data_documento->format('d/m/Y') }}</td>
              <td>
                <div class="btn-group" role="group">
                  <a href="{{ route('documento.show', $documento) }}" class="btn btn-outline-success btn-sm mr-2 d-flex">
                    <i class="fas fa-eye"></i>
                  </a>
                  @unless ($documento->finalizado)
                    <a href="{{ route('documento.edit', $documento) }}" class="btn btn-outline-primary btn-sm mr-2 d-flex">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('documento.destroy', $documento) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm d-flex" onclick="return confirm('Tem certeza?')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  @endunless
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
