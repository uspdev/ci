@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <div class="h4 mb-0">
          <a href="{{ route('categoria.index') }}">Categorias</a>
          <i class="fas fa-angle-right fa-sm"></i> {{ $categoria->nome }}
        </div>
        <a href="{{ route('categoria.create.doc', $categoria) }}" class="btn btn-outline-success">
          <i class="fas fa-plus"></i> Novo
        </a>
        <div>
          <span class="p-3"></span>
          @include('documento.partials.ano-select')
        </div>
      </div>
      <div>
        @include('categoria.partials.editar-btn')
      </div>
    </div>
    <div class="card-body">
      <table id="documentos-table" class="table table-hover datatable-simples table-bordered">
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
              <td>
                <a href="{{ route('documento.show', $documento) }}">
                  {{ $documento->codigo }}
                </a>
              </td>
              <td>{{ $documento->data_documento->format('d/m/Y') }}</td>
              <td>{{ $documento->destinatario }}</td>
              <td>{{ $documento->remetente }}</td>
              <td title="{{ $documento->assunto }}">
                {{ Str::limit($documento->assunto, 75) }}
              </td>
              <td>
                <div class="btn-group" role="group">
                  <a href="{{ route('documento.show', $documento) }}" class="btn btn-outline-success btn-sm mr-2 d-flex"
                    title="Ver Documento">
                    <i class="fas fa-eye"></i>
                  </a>
                  <form action="{{ route('documento.copy', $documento) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm mr-2 d-flex" title="Copiar Documento">
                      <i class="fas fa-copy"></i>
                    </button>
                  </form>

                  @unless ($documento->finalizado)
                    <a href="{{ route('documento.edit', $documento) }}" class="btn btn-outline-primary btn-sm mr-2 d-flex"
                      title="Editar documento">
                      <i class="fas fa-edit"></i>
                    </a>
                  @else
                    <button type="button" class="btn btn btn-warning btn-sm mr-2 d-flex" disabled
                      title="Documento finalizado">
                      <i class="fas fa-lock"></i>
                    </button>
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
