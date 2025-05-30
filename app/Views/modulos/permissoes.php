<?php include 'app/Views/include/nav.php' ?>

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
                    <?= Helper::mensagem('permissao') ?>
                    <?= Helper::mensagemSweetAlert('permissao') ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-shield-alt me-2"></i> Gerenciar Permissões
                        </h1>
                        <a href="<?= URL ?>/usuarios/listar" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Voltar
                        </a>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border" id="tituloMenu">
                                    <h3 class="box-title">
                                        <i class="fas fa-user me-2"></i> 
                                        Permissões para: <?= $dados['usuario']->nome ?> 
                                        <!-- <span class="badge bg-<?= $dados['usuario']->perfil == 'admin' ? 'danger' : ($dados['usuario']->perfil == 'analista' ? 'warning' : 'info') ?> ms-2">
                                            <?= ucfirst($dados['usuario']->perfil) ?>
                                        </span> -->
                                    </h3>
                                    
                                    <!-- Campo de Pesquisa -->
                                    <div class="mt-3">
                                        <div class="input-group">
                                            <input type="text" id="pesquisaModulo" class="form-control" placeholder="Pesquisar módulo...">
                                            <button class="btn btn-primary btn-sm" type="button" onclick="pesquisarModulos()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- fim box-header -->
                                <fieldset aria-labelledby="tituloMenu">
                                    <div class="card-body">
                                        <form action="<?= URL ?>/usuarios/salvarPermissoes/<?= $dados['usuario']->id ?>" method="POST">
                                            <div class="table-responsive">
                                                <table class="table table-hover" id="tabelaPermissoes">
                                                    <thead class="cor-fundo-azul-escuro text-white">
                                                        <tr>
                                                            <th width="5%">ID</th>
                                                            <th width="15%">Ícone</th>
                                                            <th width="40%">Módulo</th>
                                                            <th width="20%">Tipo</th>
                                                            <th width="20%">Permissão</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($dados['modulos'] as $modulo): ?>
                                                            <tr class="table-secondary">
                                                                <td><?= $modulo['id'] ?></td>
                                                                <td><i class="<?= $modulo['icone'] ?> fa-lg"></i></td>
                                                                <td><strong><?= $modulo['nome'] ?></strong></td>
                                                                <td><span class="badge bg-primary">Principal</span></td>
                                                                <td>
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" 
                                                                            name="modulos[]" 
                                                                            id="modulo_<?= $modulo['id'] ?>"
                                                                            value="<?= $modulo['id'] ?>"
                                                                            <?= in_array($modulo['id'], $dados['permissoes']) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label" for="modulo_<?= $modulo['id'] ?>">
                                                                            <?= in_array($modulo['id'], $dados['permissoes']) ? 'Permitido' : 'Bloqueado' ?>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php if (!empty($modulo['submodulos'])): ?>
                                                                <?php foreach ($modulo['submodulos'] as $submodulo): ?>
                                                                    <tr class="modulo-parent-<?= $modulo['id'] ?>">
                                                                        <td><?= $submodulo['id'] ?></td>
                                                                        <td><i class="<?= $submodulo['icone'] ?> fa-lg"></i></td>
                                                                        <td class="ps-4"><?= $submodulo['nome'] ?></td>
                                                                        <td><span class="badge bg-info">Submódulo</span></td>
                                                                        <td>
                                                                            <div class="form-check form-switch">
                                                                                <input class="form-check-input" type="checkbox" 
                                                                                    name="modulos[]" 
                                                                                    id="submodulo_<?= $submodulo['id'] ?>"
                                                                                    value="<?= $submodulo['id'] ?>"
                                                                                    <?= in_array($submodulo['id'], $dados['permissoes']) ? 'checked' : '' ?>>
                                                                                <label class="form-check-label" for="submodulo_<?= $submodulo['id'] ?>">
                                                                                    <?= in_array($submodulo['id'], $dados['permissoes']) ? 'Permitido' : 'Bloqueado' ?>
                                                                                </label>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="text-end mt-3">
                                                <a href="<?= URL ?>/usuarios/listar" class="btn btn-secondary me-2 btn-sm">
                                                    <i class="fas fa-times me-2"></i> Cancelar
                                                </a>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-save me-2"></i> Salvar Permissões
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </fieldset>
                            </div><!-- fim box -->
                        </div>
                    </div> <!-- fim row -->
                </div><!-- fim col-md-9 -->
            </div>
        </section>
    </div>
</main>
<?php include 'app/Views/include/footer.php' ?>

<script>
    // Função para pesquisar módulos
    function pesquisarModulos() {
        const termoPesquisa = document.getElementById('pesquisaModulo').value.toLowerCase();
        const tabela = document.getElementById('tabelaPermissoes');
        const linhas = tabela.getElementsByTagName('tr');
        
        for (let i = 1; i < linhas.length; i++) { // Começa do 1 para pular o cabeçalho
            const linha = linhas[i];
            const colunas = linha.getElementsByTagName('td');
            
            if (colunas.length > 0) {
                const textoModulo = colunas[2].textContent.toLowerCase();
                const idModulo = colunas[0].textContent;
                
                // Verifica se o texto do módulo contém o termo de pesquisa
                if (textoModulo.includes(termoPesquisa)) {
                    linha.style.display = '';
                    
                    // Se for um módulo principal, mostra também seus submódulos
                    if (linha.classList.contains('table-secondary')) {
                        const submódulos = document.querySelectorAll('.modulo-parent-' + idModulo);
                        submódulos.forEach(sub => sub.style.display = '');
                    }
                } else {
                    // Se for um submódulo, verifica se seu módulo pai está visível
                    if (!linha.classList.contains('table-secondary')) {
                        const classesLinha = linha.className;
                        const match = classesLinha.match(/modulo-parent-(\d+)/);
                        
                        if (match) {
                            const idPai = match[1];
                            const moduloPai = document.getElementById('modulo_' + idPai);
                            
                            // Se o módulo pai estiver na pesquisa, mostra o submódulo
                            if (moduloPai && document.querySelector('.table-secondary td:first-child').textContent === idPai) {
                                linha.style.display = '';
                                continue;
                            }
                        }
                    }
                    
                    linha.style.display = 'none';
                }
            }
        }
    }
    
    // Adiciona evento de pesquisa ao pressionar Enter no campo de pesquisa
    document.getElementById('pesquisaModulo').addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            pesquisarModulos();
        }
    });
    
    // Atualiza o texto do label quando o checkbox é alterado
    document.querySelectorAll('.form-check-input').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            label.textContent = this.checked ? 'Permitido' : 'Bloqueado';
        });
    });
</script>
