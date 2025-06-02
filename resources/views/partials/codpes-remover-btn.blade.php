@if ($codpes != auth()->user()->codpes)
  <span class="action-hide">
    <button class="remover-codpes-btn btn btn-sm p-0" data-codpes="{{ $codpes }}">
      <i class="fas fa-trash text-danger"></i>
    </button>
    <input type="hidden" name="codpes_rem" value="0">
  </span>
@endif

@once
  @section('javascripts_bottom')
    @parent
    <script>
      $(document).ready(function() {

        $('.remover-codpes-btn').on('click', function() {
          if (confirm('Tem certeza?')) {
            $(':input[name=codpes_rem]').val($(this).data('codpes'))
          } else {
            return false
          }
        })

      })
    </script>
  @endsection
@endonce
