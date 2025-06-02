{{--  
  Este botão de modal foi ajustado para permitir multiplos includes 
  o form-id não deve conter "." pois interfere no javascript 
  
  a rota senhaunicaFindUsers previsa ser ajustado com a permissão correta no config/senhaunica.php
  Masakik, em 22/5/2024 
--}}

<div class="mb-3" id="uspdev-forms-pessoa-usp">
  <label for="codpes" class="form-label">Usuário</label>
  <select name="codpes" class="form-control form-control-sm" data-ajax="{{ 'ok' }}">
    <option value="0">Digite o nome ou codpes..</option>
  </select>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    let attempts = 1;
    const maxAttempts = 50; // Tenta por 5 segundos (50 * 100ms)

    const intervalId = setInterval(() => {
      if (window.jQuery) {
        clearInterval(intervalId);
        // console.error("Select carregou após " + attempts + " tentativas.");
        initSelect2();
      } else if (attempts >= maxAttempts) {
        clearInterval(intervalId);
        // console.error("jQuery não carregou após várias tentativas.");
      }
      attempts++;
    }, 100);

  });

  function initSelect2() {
    var $oSelect2 = $('#uspdev-forms-pessoa-usp').find(':input[name=codpes]');
    var dataAjax = $oSelect2.data('ajax');

    $oSelect2.select2({
      ajax: {
        url: dataAjax,
        dataType: 'json',
        delay: 1000
      },
      minimumInputLength: 4,
      theme: 'bootstrap4',
      width: 'resolve',
      language: 'pt-BR'
    });

    // Coloca o foco no campo de busca ao abrir o Select2
    $(document).on('select2:open', () => {
      document.querySelector('.select2-search__field').focus();
    });
  }
</script>
