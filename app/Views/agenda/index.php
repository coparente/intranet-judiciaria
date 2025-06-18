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
                    <?= Helper::mensagem('agenda') ?>
                    <?= Helper::mensagemSweetAlert('agenda') ?>
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border" id="tituloMenu">
                                <h3 id="tabelas" class="box-title">
                                    <i class="fas fa-calendar-alt me-2"></i> <?= $dados['tituloPagina'] ?>
                                </h3>
                                <div class="pull-right">
                                    <a href="<?= URL ?>/agenda/novoEvento" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Novo Evento
                                    </a>
                                    <a href="<?= URL ?>/agenda/gerenciarCategorias" class="btn btn-info btn-sm">
                                        <i class="fas fa-tags"></i> Gerenciar Categorias
                                    </a>
                                    <a href="<?= URL ?>/agenda/teste" class="btn btn-info btn-sm" target="_blank">
                                        <i class="fas fa-bug"></i> Teste API
                                    </a>
                                </div>
                            </div>
                            <fieldset aria-labelledby="tituloMenu">
                                <div class="card-body">
                                    
                                    <!-- Legendas das Categorias -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-palette"></i> Categorias
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <?php foreach ($dados['categorias'] as $categoria): ?>
                                                            <div class="col-6 col-md-3 mb-2">
                                                                <span class="badge me-2" 
                                                                      style="background-color: <?= $categoria->cor ?>; color: white; padding: 8px 12px;">
                                                                    <?= htmlspecialchars($categoria->nome) ?>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Calendar Container -->
                                    <div class="calendar-container">
                                        <div id="calendar-loading" class="text-center p-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando calendário...</span>
                                            </div>
                                            <div class="mt-2">Inicializando FullCalendar...</div>
                                        </div>
                                        <div id="calendar"></div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Modal para Detalhes do Evento -->
<div class="modal fade" id="modalDetalhesEvento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt"></i> Detalhes do Evento
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesEventoContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnEditarEvento">Editar</button>
                <button type="button" class="btn btn-danger" id="btnExcluirEvento">Excluir</button>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/Views/include/footer.php'; ?>

<!-- FullCalendar CSS e JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/pt-br.global.min.js'></script>

<style>
.calendar-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-top: 20px;
}

.fc-toolbar-title {
    font-size: 1.2rem !important;
    font-weight: 600 !important;
    color: #495057 !important;
}

.fc-button-primary {
    background-color: #007bff !important;
    border-color: #007bff !important;
}

.fc-event {
    border-radius: 4px !important;
    font-size: 0.85rem !important;
    border: none !important;
}

#calendar-loading {
    display: block;
}

#calendar {
    display: none;
}
</style>

<script>
let calendar = null;

// Função para mostrar/esconder loading
function mostrarLoading(mostrar) {
    const loading = document.getElementById('calendar-loading');
    const calendar = document.getElementById('calendar');
    
    if (mostrar) {
        loading.style.display = 'block';
        calendar.style.display = 'none';
    } else {
        loading.style.display = 'none'; 
        calendar.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            return;
        }
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'pt-br',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title', 
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hoje',
                month: 'Mês',
                week: 'Semana',
                day: 'Dia'
            },
            height: 'auto',
            navLinks: true,
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
            
            events: {
                url: '<?= URL ?>/agenda/eventos',
                method: 'GET',
                failure: function(error) {
                    alert('Erro ao carregar eventos da agenda.');
                }
            },
            
            // Criar novo evento ao selecionar período
            select: function(info) {
                const dataInicio = info.startStr;
                const dataFim = info.endStr;
                window.location.href = `<?= URL ?>/agenda/novoEvento?data_inicio=${dataInicio}&data_fim=${dataFim}`;
            },
            
            // Clique em evento
            eventClick: function(info) {
                mostrarDetalhesEvento(info.event.id);
            },
            
            // Loading callback
            loading: function(isLoading) {
                if (!isLoading) {
                    mostrarLoading(false);
                }
            }
        });
        
        calendar.render();
        
    } catch (error) {
        mostrarLoading(false);
        document.getElementById('calendar').innerHTML = '<div class="alert alert-danger">Erro ao inicializar calendário: ' + error.message + '</div>';
    }
});

function mostrarDetalhesEvento(eventoId) {
    if (!eventoId) {
        alert('Erro: ID do evento não fornecido');
        return;
    }
    
    const url = `<?= URL ?>/agenda/detalhesEvento/${eventoId}`;
    
    fetch(url)
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Resposta não é JSON válido');
            }
            
            return response.text();
        })
        .then(text => {
            try {
                const evento = JSON.parse(text);
                
                if (evento.erro) {
                    throw new Error(evento.erro);
                }
                
                if (!evento.titulo) {
                    throw new Error('Evento não tem título');
                }
                
                const conteudo = `
                    <div class="row">
                        <div class="col-12">
                            <h5>${evento.titulo}</h5>
                            <p><strong>Status:</strong> ${evento.status || 'N/A'}</p>
                            <p><strong>Início:</strong> ${evento.data_inicio_formatada || evento.data_inicio || 'N/A'}</p>
                            <p><strong>Fim:</strong> ${evento.data_fim_formatada || evento.data_fim || 'N/A'}</p>
                            <p><strong>Categoria:</strong> ${evento.categoria_nome || 'N/A'}</p>
                            ${evento.local ? `<p><strong>Local:</strong> ${evento.local}</p>` : ''}
                            ${evento.descricao ? `<p><strong>Descrição:</strong> ${evento.descricao}</p>` : ''}
                        </div>
                    </div>
                `;
                
                document.getElementById('detalhesEventoContent').innerHTML = conteudo;
                
                // Configurar botões
                document.getElementById('btnEditarEvento').onclick = function() {
                    window.location.href = `<?= URL ?>/agenda/editarEvento/${evento.id}`;
                };
                
                document.getElementById('btnExcluirEvento').onclick = function() {
                    if (confirm(`Deseja realmente excluir o evento "${evento.titulo}"?`)) {
                        window.location.href = `<?= URL ?>/agenda/excluirEvento/${evento.id}`;
                    }
                };
                
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('modalDetalhesEvento'));
                modal.show();
                
            } catch (parseError) {
                throw new Error('Erro ao processar dados do evento');
            }
        })
        .catch(error => {
            alert('Erro ao carregar detalhes do evento: ' + error.message);
        });
}
</script> 