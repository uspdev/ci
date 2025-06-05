@extends('layouts.app')

@section('content')
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header h4">Grupos
          @include('grupo.partials.add-btn')
        </div>
        <div class="card-body">
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th style="width: 100px">Ações</th>
                <th>Nome</th>
                <th>Descrição</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($grupos as $grupo)
                <tr>
                  <td class="d-flex justify-content-start">
                    @include('grupo.partials.edit-btn')
                    @include('grupo.partials.delete-btn')
                  </td>
                  <td>{{ $grupo->name }}</td>
                  <td>{{ $grupo->description }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    {{-- gerentes de grupo --}}
    <div class="col-md-4">
      @can('manager')
        <div class="card h-100">
          <form method="post" id="form-grupos" action="{{ route('grupo.editarGerentes') }}">
            @csrf
            @method('put')
            <div class="card-header h5 py-2">
              Gerentes
              @include('partials.codpes-adicionar-btn')
              <div class="small text-secondary">Pessoas que podem criar/remover grupos</div>
            </div>
            <div class="card-body py-1">
              @foreach ($gerentes as $gerente)
                <div class="hover-effect">
                  {{ $gerente->name }}
                  @include('partials.codpes-remover-btn', ['codpes' => $gerente->codpes])
                </div>
              @endforeach
            </div>
          </form>
        </div>
      @endcan
    </div>
  </div>
@endsection
