
// Função para alterar o tipo de anexo
document.querySelectorAll('.tipo-anexo').forEach(select => {
  select.addEventListener('change', function() {
      const campoArquivo = this.closest('.modal-body').querySelector('.campo-arquivo');
      const inputArquivo = campoArquivo.querySelector('input[type="file"]');
      
      if (this.value === 'text') {
          campoArquivo.style.display = 'none';
          inputArquivo.removeAttribute('required');
      } else {
          campoArquivo.style.display = 'block';
          inputArquivo.setAttribute('required', 'required');
      }
  });
});

// Inicializa tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
  html: true,
  container: 'body',
  delay: {
      show: 200,
      hide: 100
  }
}));

// Inicializa Select2
$(document).ready(function() {
  $('.select2').select2({
    theme: 'bootstrap-5',
    // placeholder: "Selecione...",
    minimumInputLength: 2,
    language: 'pt-BR',
  });



  // Função para aumentar/diminuir fonte
  let fontSize = localStorage.getItem('fontSize') || 16;
  $('body').css('font-size', fontSize + 'px');

  // Função para salvar o tamanho da fonte
  function saveFontSize(size) {
    localStorage.setItem('fontSize', size);
    $('body').css('font-size', size + 'px');
  }

  $('#aumentarFonte').click(function() {
    if (fontSize < 20) {
      fontSize = parseInt(fontSize) + 2;
      saveFontSize(fontSize);
    }
  });

  $('#diminuirFonte').click(function() {
    if (fontSize > 12) {
      fontSize = parseInt(fontSize) - 2;
      saveFontSize(fontSize);
    }
  });

  // Modo escuro
  function setDarkMode(enabled) {
    if (enabled) {
      $('body').addClass('dark-mode');
      $('#toggleTheme i').removeClass('fa-moon').addClass('fa-sun');
    } else {
      $('body').removeClass('dark-mode');
      $('#toggleTheme i').removeClass('fa-sun').addClass('fa-moon');
    }
    localStorage.setItem('darkMode', enabled);
  }

  // Verifica preferência salva
  if (localStorage.getItem('darkMode') === 'true') {
    setDarkMode(true);
  }

  // Toggle do modo escuro
  $('#toggleTheme').click(function() {
    const isDarkMode = $('body').hasClass('dark-mode');
    setDarkMode(!isDarkMode);
  });

  // Verifica preferência do sistema
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    if (localStorage.getItem('darkMode') === null) {
      setDarkMode(true);
    }
  }

  // Monitora mudanças na preferência do sistema
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (localStorage.getItem('darkMode') === null) {
      setDarkMode(e.matches);
    }
  });
});


// Função para limpar os campos de pesquisa
function limparCampos() {
  document.getElementById('numero_processo').value = '';
  document.getElementById('numero_guia').value = '';
}

// Função para validar CPF/CNPJ
$("#cpfcnpj").keydown(function () {
  try {
      $("#cpfcnpj").unmask();
  } catch (e) { }

  var tamanho = $("#cpfcnpj").val().length;

  if (tamanho < 11) {
      $("#cpfcnpj").mask("999.999.999-99");
  } else {
      $("#cpfcnpj").mask("99.999.999/9999-99");
  }
  // ajustando foco
  var elem = this;
  setTimeout(function () {
      // mudo a posição do seletor
      elem.selectionStart = elem.selectionEnd = 10000;
  }, 0);
  // reaplico o valor para mudar o foco
  var currentValue = $(this).val();
  $(this).val('');
  $(this).val(currentValue);
});

// mascara o cpfcnpj editar parte
$("#cpfcnpjEditar").keydown(function () {
  try {
      $("#cpfcnpjEditar").unmask();
  } catch (e) { }

  var tamanho = $("#cpfcnpjEditar").val().length;

  if (tamanho < 11) {
      $("#cpfcnpjEditar").mask("999.999.999-99");
  } else {
      $("#cpfcnpjEditar").mask("99.999.999/9999-99");
  }
  // ajustando foco
  var elem = this;
  setTimeout(function () {
      // mudo a posição do seletor
      elem.selectionStart = elem.selectionEnd = 10000;
  }, 0);
  // reaplico o valor para mudar o foco
  var currentValue = $(this).val();
  $(this).val('');
  $(this).val(currentValue);
});

//Mascaras formulario de cadastro processual
$("#telefone, #telefoneEditar").mask("(00) 00000-0000"); //000 000 0000 eua
$("#n_processo").mask("9999999-99.9999.9.99.9999");
$("#n_guia").mask("99999999-9/99");

// Aplicando máscara para campos de valor monetário
// $(document).ready(function() {
//   // Máscara para o campo valor
//   $("#valor, .money").mask('#.##0,00', {
//     reverse: true,
//     placeholder: "0,00"
//   });
// });
