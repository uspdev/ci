@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <h4 class="mb-0">Documentos em {{ \App\Models\Categoria::find($categoria)->nome }}</h4>
        <div class="dropdown ml-2 mt-1">
          <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="novoDocumentoDropdown"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ $ano }}
          </button>
          <div class="dropdown-menu" aria-labelledby="novoDocumentoDropdown">
            @foreach ($anos as $ano)
              <a class="dropdown-item" href="{{ route('categoria.docs', ['categoria' => $categoria, 'ano' => $ano]) }}">
                {{ $ano }}
              </a>
            @endforeach
          </div>
        </div>
      </div>
      <a href="{{ route('categoria.create.doc', $categoria) }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Novo Documento
      </a>
    </div>
    <div class="card-body">
      <table id="documentos-table" class="table table-striped table-bordered">
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
                    <a href="{{ route('documento.edit', $documento) }}"
                      class="btn btn-outline-primary btn-sm mr-2 d-flex">
                      <i class="fas fa-edit"></i>
                    </a>
                    {{-- <form action="{{ route('documento.destroy', $documento) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm d-flex"
                        onclick="return confirm('Tem certeza?')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form> --}}
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
