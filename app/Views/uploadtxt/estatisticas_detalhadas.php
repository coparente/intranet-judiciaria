<?php include 'app/Views/include/nav.php' ?>

<?php
// Função auxiliar para construir URLs de paginação
function construirUrlPaginacao($pagina)
{
    $params = $_GET;
    $params['pagina'] = $pagina;
    return URL . '/uploadtxt/estatisticasDetalhadas?' . http_build_query($params);
}
?>

<main>
    <div class="content">
        <section class="content">
            <div class="row">
                <div class="col-md-3">
                    <!-- Menu Lateral -->
                    <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] == 'admin'): ?>
                        <?php include 'app/Views/include/menu_adm.php' ?>
                    <?php endif; ?>
                    <?php include 'app/Views/include/menu.php' ?>
                </div>

                <!-- Conteúdo Principal -->
                <div class="col-md-9">
                    <!-- Alertas e Mensagens -->
                    <?= Helper::mensagem('uploadtxt') ?>

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-chart-line me-2"></i> <?= $dados['tituloPagina'] ?>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?= URL ?>/dashboard/inicial">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= URL ?>/uploadtxt/index">Upload TXT</a></li>
                                <li class="breadcrumb-item"><a href="<?= URL ?>/uploadtxt/estatisticas">Estatísticas</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Detalhadas</li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Filtros -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-filter me-2"></i> Filtros
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?= URL ?>/uploadtxt/estatisticasDetalhadas" class="row g-3">
                                <div class="col-md-3">
                                    <label for="data_inicio" class="form-label">Data Inicial</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio"
                                        value="<?= $dados['filtros']['data_inicio'] ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="data_fim" class="form-label">Data Final</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim"
                                        value="<?= $dados['filtros']['data_fim'] ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="responsavel" class="form-label">Responsável</label>
                                    <select class="form-control" id="responsavel" name="responsavel">
                                        <option value="">Todos</option>
                                        <?php foreach ($dados['responsaveis'] as $resp): ?>
                                            <option value="<?= $resp->nome ?>" <?= $dados['filtros']['responsavel'] == $resp->nome ? 'selected' : '' ?>>
                                                <?= $resp->nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="comarca" class="form-label">Comarca</label>
                                    <select class="form-control" id="comarca" name="comarca">
                                        <option value="">Todas</option>
                                        <?php foreach ($dados['comarcas'] as $com): ?>
                                            <option value="<?= $com->comarca ?>" <?= $dados['filtros']['comarca'] == $com->comarca ? 'selected' : '' ?>>
                                                <?= $com->comarca ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="movimentacao" class="form-label">Tipo de Movimentação</label>
                                    <select class="form-control" id="movimentacao" name="movimentacao">
                                        <option value="">Todos</option>
                                        <?php foreach ($dados['tiposMovimentacao'] as $mov): ?>
                                            <option value="<?= $mov->movimentacao ?>" <?= $dados['filtros']['movimentacao'] == $mov->movimentacao ? 'selected' : '' ?>>
                                                <?= $mov->movimentacao ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <br>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i> Filtrar
                                    </button>
                                    <a href="<?= URL ?>/uploadtxt/estatisticasDetalhadas" class="btn btn-secondary">
                                        <i class="fas fa-eraser me-2"></i> Limpar Filtros
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Resumo Estatístico -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Movimentações</h5>
                                    <p class="display-4"><?= $dados['totalMovimentacoes'] ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Processos</h5>
                                    <p class="display-4"><?= $dados['totalProcessosUnicos'] ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Comarcas</h5>
                                    <p class="display-4"><?= $dados['totalComarcasUnicas'] ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-dark">Responsáveis</h5>
                                    <p class="display-4"><?= $dados['totalResponsaveisUnicos'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas Diárias -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-calendar-day me-2"></i> Estatísticas Diárias
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Data</th>
                                            <th class="text-center">Movimentações</th>
                                            <th class="text-center">Processos</th>
                                            <th class="text-center">Comarcas</th>
                                            <th class="text-center">Responsáveis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dados['estatisticasDiarias'])): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Nenhum dado encontrado</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($dados['estatisticasDiarias'] as $estatistica): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($estatistica->dia)) ?></td>
                                                    <td class="text-center"><?= $estatistica->total_movimentacoes ?></td>
                                                    <td class="text-center"><?= $estatistica->total_processos ?></td>
                                                    <td class="text-center"><?= $estatistica->total_comarcas ?></td>
                                                    <td class="text-center"><?= $estatistica->total_responsaveis ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Movimentações -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header cor-fundo-azul-escuro">
                            <div class="row">
                                <div class="col-md-9">
                                    <h5 class="card-title mb-0 text-white">
                                        <i class="fas fa-list me-2"></i> Movimentações Detalhadas
                                    </h5>
                                </div>
                                <div class="col-md-3">

                                    <!-- Seletor de itens por página -->
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
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="tabelaMovimentacoes" class="table table-striped table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Processo</th>
                                            <th>Comarca</th>
                                            <th>Movimentação</th>
                                            <th>Data/Hora</th>
                                            <th>Responsável</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($dados['movimentacoes'])): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Nenhum registro encontrado</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($dados['movimentacoes'] as $mov): ?>
                                                <tr>
                                                    <td><?= $mov->numero ?></td>
                                                    <td><?= $mov->comarca ?></td>
                                                    <td><?= $mov->movimentacao ?></td>
                                                    <td><?= date('d/m/Y H:i:s', strtotime($mov->data)) ?></td>
                                                    <td><?= $mov->nome ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Após a tabela de movimentações detalhadas, adicione os controles de paginação -->
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <p class="mb-0">
                                    Mostrando <?= count($dados['movimentacoes']) ?> de <?= $dados['paginacao']['total_registros'] ?> registros
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

                                            // Sempre mostrar a última página se houver mais de uma
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

                    <!-- Botões de Ação -->
                    <div class="mb-4">
                        <a href="<?= URL ?>/uploadtxt/estatisticas" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Voltar para Estatísticas
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
        window.location.href = '<?= URL ?>/uploadtxt/estatisticasDetalhadas?' + urlParams.toString();
    }

    // Inicializar DataTables com paginação desativada (usamos nossa própria paginação)
    $(document).ready(function() {
        $('#tabelaMovimentacoes').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
            },
            "paging": false,
            "ordering": true,
            "info": false,
            "searching": true
        });
    });

    // Função para exportar tabela para Excel
    function exportarParaExcel() {
        // Obter a tabela
        let table = document.getElementById('tabelaMovimentacoes');

        // Criar um workbook
        let wb = XLSX.utils.table_to_book(table, {
            sheet: "Movimentações Detalhadas"
        });

        // Exportar para arquivo
        XLSX.writeFile(wb, 'movimentacoes_detalhadas.xlsx');
    }

    // Função para exportar tabela para PDF
    function exportarParaPDF() {
        // Configuração do PDF
        const element = document.querySelector('.card:last-of-type');
        const opt = {
            margin: 1,
            filename: 'movimentacoes_detalhadas.pdf',
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

<!-- Incluir bibliotecas para exportação e DataTables -->
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">

<?php include 'app/Views/include/footer.php' ?>