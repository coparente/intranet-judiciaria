<?php include 'app/Views/include/nav.php' ?>

<?php
// Função auxiliar para construir URLs de paginação
function construirUrlPaginacao($pagina)
{
    $params = $_GET;
    $params['pagina'] = $pagina;
    return URL . '/uploadtxt/estatisticas?' . http_build_query($params);
}
?>

<main>
    <div class="content">
        <section class="content">
            <div class="row">
                <!-- Menu Lateral -->
                <!-- <div class="col-md-3">
                    <?php //if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): 
                    ?>
                        <?php //include 'app/Views/include/menu_adm.php' 
                        ?>
                    <? php // endif; 
                    ?>
                    <? php // include 'app/Views/include/menu.php' 
                    ?>
                </div> -->

                <!-- Conteúdo Principal -->
                <div class="col-md-12">
                    <!-- Alertas e Mensagens -->
                    <?= Helper::mensagem('uploadtxt') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-chart-bar me-2"></i> <?= $dados['tituloPagina'] ?>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?= URL ?>/dashboard/inicial">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= URL ?>/uploadtxt/index">Upload TXT</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Estatísticas</li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Filtros -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-filter me-2"></i> Filtros
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URL ?>/uploadtxt/estatisticas" method="get" class="row g-3">
                                <!-- Data Início -->
                                <div class="col-md-4">
                                    <label for="data_inicio" class="form-label">Data Início</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio"
                                        value="<?= $dados['filtros']['data_inicio'] ?>">
                                </div>

                                <!-- Data Fim -->
                                <div class="col-md-4">
                                    <label for="data_fim" class="form-label">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim"
                                        value="<?= $dados['filtros']['data_fim'] ?>">
                                </div>

                                <!-- Responsável -->
                                <div class="col-md-4 mb-3">
                                    <label for="responsavel" class="form-label">Responsável</label>
                                    <select class="form-control" id="responsavel" name="responsavel">
                                        <option value="">Todos</option>
                                        <?php foreach ($dados['todosResponsaveis'] as $resp): ?>
                                            <option value="<?= $resp->nome ?>" <?= $dados['filtros']['responsavel'] == $resp->nome ? 'selected' : '' ?>>
                                                <?= $resp->nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Botões -->
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i> Filtrar
                                    </button>
                                    <a href="<?= URL ?>/uploadtxt/estatisticas" class="btn btn-secondary">
                                        <i class="fas fa-undo me-2"></i> Limpar Filtros
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Estatísticas NAC -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header cor-fundo-azul-escuro ">
                            <div class="row">
                                <div class="col-md-9">
                                    <h5 class="card-title mb-0 text-white">
                                        <i class="fas fa-table me-2"></i> MOVIMENTAÇÕES DIÁRIAS - NAC
                                    </h5>
                                </div>
                                <!-- Seletor de itens por página -->
                                <div class="col-md-3">
                                    <div class="me-3">
                                        <label for="itens_por_pagina" class="me-2 text-white">Itens por página:</label>
                                        <select id="itens_por_pagina" class="form-control form-control-sm d-inline-block w-auto"
                                            onchange="alterarItensPorPagina(this.value)">
                                            <?php foreach ($dados['opcoesItensPorPagina'] as $opcao): ?>
                                                <option value="<?= $opcao ?>" <?= $dados['filtros']['itens_por_pagina'] == $opcao ? 'selected' : '' ?>>
                                                    <?= $opcao ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>RESPONSÁVEL</th>
                                        <?php foreach ($dados['movimentacoes'] as $movimentacao): ?>
                                            <th class="text-center"><?= $movimentacao->movimentacao ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-center bg-light">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dados['responsaveis'] as $responsavel): ?>
                                        <tr>
                                            <td><?= $responsavel->nome ?></td>
                                            <?php foreach ($dados['movimentacoes'] as $movimentacao): ?>
                                                <td class="text-center">
                                                    <?= $dados['estatisticasNAC'][$responsavel->nome][$movimentacao->movimentacao] ?? 0 ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="text-center fw-bold bg-light">
                                                <?= $dados['estatisticasNAC'][$responsavel->nome]['total'] ?? 0 ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th>Total</th>
                                        <?php foreach ($dados['movimentacoes'] as $movimentacao): ?>
                                            <th class="text-center">
                                                <?= $dados['estatisticasNAC']['totais'][$movimentacao->movimentacao] ?? 0 ?>
                                            </th>
                                        <?php endforeach; ?>
                                        <th class="text-center">
                                            <?= $dados['estatisticasNAC']['totais']['total'] ?? 0 ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Controles de Paginação -->
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <p class="mb-0">
                                    Mostrando <?= count($dados['responsaveis']) ?> de <?= $dados['paginacao']['total_registros'] ?> responsáveis
                                    (Página <?= $dados['paginacao']['pagina_atual'] ?> de <?= $dados['paginacao']['total_paginas'] ?>)
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end align-items-center">


                                    <!-- Navegação de páginas -->
                                    <nav aria-label="Navegação de páginas">
                                        <ul class="pagination pagination-sm mb-0">
                                            <!-- Botão Anterior -->
                                            <li class="page-item <?= $dados['paginacao']['pagina_atual'] <= 1 ? 'disabled' : '' ?>">
                                                <a class="page-link" href="<?= construirUrlPaginacao($dados['paginacao']['pagina_atual'] - 1) ?>" aria-label="Anterior">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>

                                            <!-- Páginas -->
                                            <?php
                                            $paginaAtual = $dados['paginacao']['pagina_atual'];
                                            $totalPaginas = $dados['paginacao']['total_paginas'];

                                            // Determinar quais páginas mostrar
                                            $paginasParaMostrar = [];

                                            // Sempre mostrar a primeira página
                                            $paginasParaMostrar[] = 1;

                                            // Mostrar páginas ao redor da página atual
                                            for ($i = max(2, $paginaAtual - 2); $i <= min($totalPaginas - 1, $paginaAtual + 2); $i++) {
                                                $paginasParaMostrar[] = $i;
                                            }

                                            if ($totalPaginas > 1) {
                                                $paginasParaMostrar[] = $totalPaginas;
                                            }

                                            // Ordenar e remover duplicatas
                                            $paginasParaMostrar = array_unique($paginasParaMostrar);
                                            sort($paginasParaMostrar);

                                            // Variável para controlar quando adicionar reticências
                                            $ultimaPaginaMostrada = 0;

                                            foreach ($paginasParaMostrar as $pagina) {
                                                // Adicionar reticências se houver lacunas
                                                if ($pagina - $ultimaPaginaMostrada > 1) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }

                                                // Adicionar link para a página
                                                echo '<li class="page-item ' . ($pagina == $paginaAtual ? 'active' : '') . '">';
                                                echo '<a class="page-link" href="' . construirUrlPaginacao($pagina) . '">' . $pagina . '</a>';
                                                echo '</li>';

                                                $ultimaPaginaMostrada = $pagina;
                                            }
                                            ?>

                                            <!-- Botão Próximo -->
                                            <li class="page-item <?= $dados['paginacao']['pagina_atual'] >= $dados['paginacao']['total_paginas'] ? 'disabled' : '' ?>">
                                                <a class="page-link" href="<?= construirUrlPaginacao($dados['paginacao']['pagina_atual'] + 1) ?>" aria-label="Próximo">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="mb-4">
                    <a href="<?= URL ?>/uploadtxt/index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Voltar
                    </a>
                    <a href="<?= URL ?>/uploadtxt/estatisticasDetalhadas" class="btn btn-info">
                        <i class="fas fa-chart-line me-2"></i> Estatísticas Detalhadas
                    </a>
                    <!-- <button class="btn btn-success" onclick="exportarParaExcel()">
                        <i class="fas fa-file-excel me-2"></i> Exportar para Excel
                    </button>
                    <button class="btn btn-danger" onclick="exportarParaPDF()">
                        <i class="fas fa-file-pdf me-2"></i> Exportar para PDF
                    </button> -->
                </div>
            </div>
    </div>
    </section>
    </div>
</main>

<script>
    // Função para alterar itens por página
    function alterarItensPorPagina(valor) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('itens_por_pagina', valor);
        urlParams.set('pagina', 1); // Voltar para a primeira página ao alterar itens por página
        window.location.href = '<?= URL ?>/uploadtxt/estatisticas?' + urlParams.toString();
    }

    // Função para exportar tabela para Excel
    function exportarParaExcel() {
        // Obter a tabela
        let table = document.querySelector('table');

        // Criar um workbook
        let wb = XLSX.utils.table_to_book(table, {
            sheet: "Estatísticas NAC"
        });

        // Exportar para arquivo
        XLSX.writeFile(wb, 'estatisticas_nac.xlsx');
    }

    // Função para exportar tabela para PDF
    function exportarParaPDF() {
        // Configuração do PDF
        const element = document.querySelector('.card');
        const opt = {
            margin: 1,
            filename: 'estatisticas_nac.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'cm',
                format: 'a4',
                orientation: 'landscape'
            }
        };

        // Gerar PDF
        html2pdf().set(opt).from(element).save();
    }
</script>

<!-- Incluir bibliotecas para exportação -->
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<?php include 'app/Views/include/footer.php' ?>