<!-- Footer Institucional -->
<footer class="p-1 text-white cor-fundo-azul-extra-escuro">
  <div class="container">
    <div class="mt-3">
    </div>
    <div class="border-top text-center mt-3">
      &COPY; 2025 -
      <?= date('Y') ?> Diretoria Judiciária / Versão
      <?= APP_VERSAO ?>
    </div>
  </div>
</footer>

<script>
// Script para habilitar rolagem suave para as âncoras
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se estamos na página do dashboard
    if (window.location.href.includes('dashboard')) {
        // Captura todos os links que apontam para âncoras na página
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (!targetElement) return;
                
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            });
        });
    }
});
</script>

</body>
</html>