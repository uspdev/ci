<div class="card activities mt-3">
  <div class="card-header h5">Histórico</div>
  <div class="card-body">
    @foreach ($activities as $activity)
      {{ $activity->created_at->format('d/m/Y H:i:s') }} |
      <strong> <a href="{{ route('documento.atividade', $activity) }}">
          {{ $activity->description }}</a>
      </strong>
      por {{ \App\Models\User::findOrFail($activity->causer_id)->name }}
      em {{ $activity->properties['agent']['ip'] }}
      <br>
    @endforeach
  </div>
</div>
