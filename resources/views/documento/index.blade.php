@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <h4 class="mb-0"><a href="{{ route('categoria.index') }}">Categorias</a> >
          {{ $categoria->nome }}</h4>
        <div class="dropdown ml-2 mt-1">
          <a href="{{ route('categoria.create.doc', $categoria) }}" class="btn btn-outline-success">
            <i class="fas fa-plus"></i> Novo
          </a>
        </div>
      </div>
      <div>
        <button class="btn btn-info dropdown-toggle" type="button" id="anoDropdown" data-toggle="dropdown"
          aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-calendar"></i> {{ $ano }}
        </button>
        <div class="dropdown-menu" aria-labelledby="anoDropdown">
          @foreach ($anos as $ano)
            <a class="dropdown-item" href="{{ route('categoria.docs', ['categoria' => $categoria, 'ano' => $ano]) }}">
              {{ $ano }}
            </a>
          @endforeach
        </div>
      </div>
    </div>
    <div class="card-body">
      <table id="documentos-table" class="table datatable-simples table-striped table-bordered">
        <thead>
          <tr>
            <th>Sequencial</th>
            <th>Código</th>
            <th>Data</th>
            <th>Destinatário</th>
            <th>Remetente</th>
            <th>Assunto</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($documentos as $documento)
            <tr>
              <td>{{ $documento->sequencial }}</td>
              <td>{{ $documento->codigo }}</td>
              <td>{{ $documento->data_documento->format('d/m/Y') }}</td>
              <td>{{ $documento->destinatario }}</td>
              <td>{{ $documento->remetente }}</td>
              <td>{{ Str::limit($documento->assunto, 30) }}</td>
              <td>
                <div class="btn-group" role="group">
                  <a href="{{ route('documento.show', $documento) }}" class="btn btn-outline-success btn-sm mr-2 d-flex">
                    <i class="fas fa-eye"></i>
                  </a>
                  <form action="{{ route('documento.copy', $documento) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm mr-2 d-flex">
                      <i class="fas fa-copy"></i>
                    </button>
                  </form>
                  @unless ($documento->finalizado)
                    <a href="{{ route('documento.edit', $documento) }}" class="btn btn-outline-primary btn-sm mr-2 d-flex">
                      <i class="fas fa-edit"></i>
                    </a>
                  @else
                    <button type="button" class="btn btn btn-warning btn-sm mr-2 d-flex" disabled><i
                        class="fas fa-lock"></i></button>
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
