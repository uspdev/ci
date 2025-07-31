@extends('layouts.app')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><a href="{{ route('template.index') }}">Templates</a> >
        {{ $template->nome }}</h4>
      <a href="{{ route('template.edit', $template) }}" class="btn btn-primary">Editar</a>
    </div>
    <div class="card-body">
      <p><strong>Descrição:</strong> {{ $template->descricao ?? '-' }}</p>
      <p><strong>Categorias:</strong>
        @foreach ($template->categorias as $categoria)
          <span class="badge bg-secondary text-white">{{ $categoria->nome }}</span>
        @endforeach
      </p>
      <hr>
      <h5>Conteúdo Padrão</h5>
      <div class="border p-2 bg-light" style="white-space: pre-wrap;">{!! nl2br(e($template->conteudo_padrao)) !!}</div>
      <hr>
      <a href="{{ route('template.index') }}" class="btn btn-secondary mt-3">Voltar</a>
      <a href="{{ route('template.gerarPdf', $template) }}" class="btn btn-outline-secondary mt-3">Exemplo</a>
    </div>
  </div>
@endsection
