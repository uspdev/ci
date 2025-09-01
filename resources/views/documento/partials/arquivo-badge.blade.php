@if ($arquivo->tipo_arquivo == 'upload')
  <span class="badge badge-success">{{ ucfirst($arquivo->tipo_arquivo) }}</span>
@else
  <span class="badge badge-primary">{{ ucfirst($arquivo->tipo_arquivo) }}</span>
@endif
