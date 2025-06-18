/**
 * AGENDA.JS - Funcionalidades JavaScript para o módulo de Agenda
 * 
 * Este arquivo contém todas as funções necessárias para:
 * - Inicializar o FullCalendar
 * - Gerenciar eventos (criar, editar, excluir, mover)
 * - Filtros e busca
 * - Modais e interfaces
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */

// Variáveis globais
let calendar = null;
let eventoAtual = null;
let categorias = [];
let filtroAtivoCategoriaId = null;

// Configurações do FullCalendar
const calendarConfig = {
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
        day: 'Dia',
        list: 'Lista'
    },
    height: 'auto',
    navLinks: true,
    editable: true,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,
    firstDay: 0, // Domingo
    slotMinTime: '06:00:00',
    slotMaxTime: '22:00:00',
    allDaySlot: true,
    allDayText: 'Dia Todo',
    slotLabelFormat: {
        hour: 'numeric',
        minute: '2-digit'
    },
    eventTimeFormat: {
        hour: 'numeric',
        minute: '2-digit'
    },
    views: {
        dayGridMonth: {
            titleFormat: { year: 'numeric', month: 'long' }
        },
        timeGridWeek: {
            titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
        },
        timeGridDay: {
            titleFormat: { year: 'numeric', month: 'long', day: 'numeric' }
        }
    }
};

/**
 * Inicializa a agenda
 */
function inicializarAgenda() {
    console.log('Inicializando agenda...');
    
    // Carrega categorias
    carregarCategorias();
    
    // Inicializa o calendário
    inicializarCalendario();
    
    // Configura event listeners
    configurarEventListeners();
    
    console.log('Agenda inicializada com sucesso!');
}

/**
 * Inicializa o FullCalendar
 */
function inicializarCalendario() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Elemento calendar não encontrado');
        return;
    }
    
    const config = {
        ...calendarConfig,
        events: {
            url: `${URL}/agenda/eventos`,
            method: 'GET',
            failure: function() {
                mostrarNotificacao('Erro ao carregar eventos da agenda', 'error');
            }
        },
        
        // Eventos do calendário
        select: function(info) {
            criarNovoEvento(info.startStr, info.endStr);
        },
        
        eventClick: function(info) {
            eventoAtual = info.event;
            mostrarDetalhesEvento(info.event.id);
        },
        
        eventDrop: function(info) {
            moverEvento(info.event.id, info.event.startStr, info.event.endStr);
        },
        
        eventResize: function(info) {
            redimensionarEvento(info.event.id, info.event.startStr, info.event.endStr);
        },
        
        loading: function(isLoading) {
            if (isLoading) {
                mostrarCarregando();
            } else {
                esconderCarregando();
            }
        }
    };
    
    calendar = new FullCalendar.Calendar(calendarEl, config);
    calendar.render();
}

/**
 * Configura os event listeners
 */
function configurarEventListeners() {
    // Filtro por categoria
    const filtroCategoria = document.getElementById('filtroCategoria');
    if (filtroCategoria) {
        filtroCategoria.addEventListener('change', function() {
            filtrarEventosPorCategoria(this.value);
        });
    }
    
    // Botões de visualização
    const btnsVisualizacao = document.querySelectorAll('.btn-view');
    btnsVisualizacao.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            if (calendar && view) {
                calendar.changeView(view);
                atualizarBotaoVisualizacaoAtivo(this);
            }
        });
    });
    
    // Botão novo evento
    const btnNovoEvento = document.getElementById('btnNovoEvento');
    if (btnNovoEvento) {
        btnNovoEvento.addEventListener('click', function() {
            criarNovoEvento();
        });
    }
}

/**
 * Carrega as categorias do servidor
 */
function carregarCategorias() {
    fetch(`${URL}/agenda/listarCategorias`)
        .then(response => response.json())
        .then(data => {
            categorias = data;
            console.log('Categorias carregadas:', categorias);
        })
        .catch(error => {
            console.error('Erro ao carregar categorias:', error);
            mostrarNotificacao('Erro ao carregar categorias', 'error');
        });
}

/**
 * Cria um novo evento
 */
function criarNovoEvento(dataInicio = null, dataFim = null) {
    let url = `${URL}/agenda/novoEvento`;
    
    if (dataInicio && dataFim) {
        url += `?data_inicio=${dataInicio}&data_fim=${dataFim}`;
    }
    
    window.location.href = url;
}

/**
 * Mostra detalhes de um evento
 */
function mostrarDetalhesEvento(eventoId) {
    mostrarCarregandoModal();
    
    fetch(`${URL}/agenda/detalhesEvento/${eventoId}`)
        .then(response => response.json())
        .then(evento => {
            if (evento.erro) {
                mostrarNotificacao(evento.erro, 'error');
                return;
            }
            
            renderizarDetalhesEvento(evento);
            mostrarModal('modalDetalhesEvento');
        })
        .catch(error => {
            console.error('Erro ao carregar detalhes:', error);
            mostrarNotificacao('Erro ao carregar detalhes do evento', 'error');
        });
}

/**
 * Renderiza os detalhes do evento no modal
 */
function renderizarDetalhesEvento(evento) {
    const statusBadge = obterBadgeStatus(evento.status);
    const conteudo = `
        <div class="evento-detalhe">
            <div class="row">
                <div class="col-md-8">
                    <div class="evento-label">Título:</div>
                    <div class="evento-valor">${evento.titulo}</div>
                </div>
                <div class="col-md-4">
                    <div class="evento-label">Status:</div>
                    <div class="evento-valor">${statusBadge}</div>
                </div>
            </div>
        </div>
        
        <div class="evento-detalhe">
            <div class="row">
                <div class="col-md-6">
                    <div class="evento-label">Data/Hora Início:</div>
                    <div class="evento-valor">${evento.data_inicio_formatada}</div>
                </div>
                <div class="col-md-6">
                    <div class="evento-label">Data/Hora Fim:</div>
                    <div class="evento-valor">${evento.data_fim_formatada}</div>
                </div>
            </div>
        </div>
        
        <div class="evento-detalhe">
            <div class="row">
                <div class="col-md-6">
                    <div class="evento-label">Categoria:</div>
                    <div class="evento-valor">
                        <span class="badge" style="background-color: ${evento.categoria_cor}">
                            ${evento.categoria_nome}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="evento-label">Criado por:</div>
                    <div class="evento-valor">${evento.usuario_nome || 'N/A'}</div>
                </div>
            </div>
        </div>
        
        ${evento.local ? `
        <div class="evento-detalhe">
            <div class="evento-label">Local:</div>
            <div class="evento-valor">${evento.local}</div>
        </div>
        ` : ''}
        
        ${evento.descricao ? `
        <div class="evento-detalhe">
            <div class="evento-label">Descrição:</div>
            <div class="evento-valor">${evento.descricao}</div>
        </div>
        ` : ''}
        
        ${evento.observacoes ? `
        <div class="evento-detalhe">
            <div class="evento-label">Observações:</div>
            <div class="evento-valor">${evento.observacoes}</div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('detalhesEventoContent').innerHTML = conteudo;
    
    // Configura botões do modal
    const btnEditar = document.getElementById('btnEditarEvento');
    const btnExcluir = document.getElementById('btnExcluirEvento');
    
    if (btnEditar) {
        btnEditar.onclick = function() {
            window.location.href = `${URL}/agenda/editarEvento/${evento.id}`;
        };
    }
    
    if (btnExcluir) {
        btnExcluir.onclick = function() {
            confirmarExclusaoEvento(evento.id, evento.titulo);
        };
    }
}

/**
 * Obtém o badge de status do evento
 */
function obterBadgeStatus(status) {
    const badges = {
        'agendado': '<span class="badge status-agendado">Agendado</span>',
        'confirmado': '<span class="badge status-confirmado">Confirmado</span>',
        'cancelado': '<span class="badge status-cancelado">Cancelado</span>',
        'concluido': '<span class="badge status-concluido">Concluído</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Indefinido</span>';
}

/**
 * Confirma a exclusão de um evento
 */
function confirmarExclusaoEvento(eventoId, titulo) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: `Deseja realmente excluir o evento "${titulo}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `${URL}/agenda/excluirEvento/${eventoId}`;
        }
    });
}

/**
 * Move um evento (drag & drop)
 */
function moverEvento(eventoId, novoInicio, novoFim) {
    fetch(`${URL}/agenda/moverEvento`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: eventoId,
            start: novoInicio,
            end: novoFim
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.sucesso) {
            mostrarNotificacao('Evento movido com sucesso!', 'success');
        } else {
            mostrarNotificacao(result.mensagem || 'Erro ao mover evento', 'error');
            calendar.refetchEvents(); // Reverte a mudança
        }
    })
    .catch(error => {
        console.error('Erro ao mover evento:', error);
        mostrarNotificacao('Erro ao mover evento', 'error');
        calendar.refetchEvents(); // Reverte a mudança
    });
}

/**
 * Redimensiona um evento
 */
function redimensionarEvento(eventoId, novoInicio, novoFim) {
    // Usa a mesma função de mover evento
    moverEvento(eventoId, novoInicio, novoFim);
}

/**
 * Filtra eventos por categoria
 */
function filtrarEventosPorCategoria(categoriaId) {
    filtroAtivoCategoriaId = categoriaId;
    
    if (calendar) {
        calendar.refetchEvents();
    }
}

/**
 * Atualiza o botão de visualização ativo
 */
function atualizarBotaoVisualizacaoAtivo(botaoAtivo) {
    const todosOsBotoes = document.querySelectorAll('.btn-view');
    todosOsBotoes.forEach(btn => {
        btn.classList.remove('active');
    });
    botaoAtivo.classList.add('active');
}

/**
 * Mostra notificação
 */
function mostrarNotificacao(mensagem, tipo = 'info') {
    const icones = {
        'success': 'success',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };
    
    Swal.fire({
        icon: icones[tipo] || 'info',
        title: mensagem,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

/**
 * Mostra modal
 */
function mostrarModal(modalId) {
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

/**
 * Esconde modal
 */
function esconderModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

/**
 * Mostra carregando no modal
 */
function mostrarCarregandoModal() {
    const conteudo = `
        <div class="agenda-loading">
            <div class="agenda-spinner"></div>
        </div>
    `;
    document.getElementById('detalhesEventoContent').innerHTML = conteudo;
}

/**
 * Mostra indicador de carregamento
 */
function mostrarCarregando() {
    const loading = document.getElementById('agenda-loading');
    if (loading) {
        loading.style.display = 'flex';
    }
}

/**
 * Esconde indicador de carregamento
 */
function esconderCarregando() {
    const loading = document.getElementById('agenda-loading');
    if (loading) {
        loading.style.display = 'none';
    }
}

/**
 * Atualiza o calendário
 */
function atualizarCalendario() {
    if (calendar) {
        calendar.refetchEvents();
    }
}

/**
 * Navega para data específica
 */
function navegarParaData(data) {
    if (calendar) {
        calendar.gotoDate(data);
    }
}

/**
 * Altera visualização do calendário
 */
function alterarVisualizacao(view) {
    if (calendar) {
        calendar.changeView(view);
    }
}

/**
 * Busca eventos por texto
 */
function buscarEventos(texto) {
    // Implementar busca de eventos se necessário
    console.log('Buscando eventos por:', texto);
}

/**
 * Exporta eventos para CSV
 */
function exportarEventosCSV() {
    // Implementar exportação se necessário
    console.log('Exportando eventos para CSV...');
}

/**
 * Funções de utilidade
 */
const AgendaUtils = {
    /**
     * Formata data para exibição
     */
    formatarData: function(data, formato = 'DD/MM/YYYY HH:mm') {
        return moment(data).format(formato);
    },
    
    /**
     * Valida se uma data é válida
     */
    validarData: function(data) {
        return moment(data).isValid();
    },
    
    /**
     * Obtém cor da categoria por ID
     */
    obterCorCategoria: function(categoriaId) {
        const categoria = categorias.find(cat => cat.id == categoriaId);
        return categoria ? categoria.cor : '#6c757d';
    },
    
    /**
     * Obtém nome da categoria por ID
     */
    obterNomeCategoria: function(categoriaId) {
        const categoria = categorias.find(cat => cat.id == categoriaId);
        return categoria ? categoria.nome : 'Sem categoria';
    }
};

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('calendar')) {
        inicializarAgenda();
    }
});

// Exporta funções para uso global
window.Agenda = {
    inicializar: inicializarAgenda,
    atualizar: atualizarCalendario,
    navegarPara: navegarParaData,
    alterarView: alterarVisualizacao,
    buscar: buscarEventos,
    exportarCSV: exportarEventosCSV,
    utils: AgendaUtils
}; 