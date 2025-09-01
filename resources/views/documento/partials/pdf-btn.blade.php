@if (isset($documento->template))

{{-- <a href="{{ route('documento.pdf', $documento) }}" class="btn btn-sm btn-outline-secondary" target="_blank"
  title="Gerar documento">
  <i class="fas fa-file-pdf"></i> Gerar PDF
</a> --}}

<a href="{{ route('documento.pdf', $documento) }}"
   class="btn btn-sm btn-outline-secondary"
   onclick="recarregarDepois(this.href)"
   target="_blank"
   title="Gerar documento">
   <i class="fas fa-file-pdf"></i> Gerar PDF
</a>

<script>
  function recarregarDepois(url) {
    // abre o PDF em nova aba
    window.open(url, '_blank');

    // espera um tempo para o download/geração terminar
    setTimeout(() => {
      window.location.reload();
    }, 2000); // 2 segundos (ajuste conforme necessário)
  }
</script>
@endif
