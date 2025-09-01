<button class="btn btn-info dropdown-toggle" type="button" id="anoDropdown" data-toggle="dropdown" aria-haspopup="true"
  aria-expanded="false">
  <i class="fas fa-calendar"></i> {{ $ano }}
</button>
<div class="dropdown-menu" aria-labelledby="anoDropdown">
  @foreach ($anos as $ano)
    <a class="dropdown-item" href="{{ route('categoria.show', ['categoria' => $categoria, 'ano' => $ano]) }}">
      {{ $ano }}
    </a>
  @endforeach
</div>
