/**
 * Classe responsável por gerenciar as notificações no frontend
 */
let gerenciadorNotificacoes;

class GerenciadorNotificacoes {
    constructor() {
        this.containerNotificacoes = document.getElementById('notificacoes-container');
        this.contadorNotificacoes = document.getElementById('contador-notificacoes');
        this.btnNotificacoes = document.getElementById('btn-notificacoes');
        this.intervaloAtualizacao = 30000; // 30 segundos
        this.intervaloPrazos = 300000; // 5 minutos
        
        if (this.btnNotificacoes) {
            this.btnNotificacoes.addEventListener('click', (e) => {
                e.preventDefault();
                this.containerNotificacoes.classList.toggle('d-none');
            });

            // Fechar ao clicar fora
            document.addEventListener('click', (e) => {
                if (!this.containerNotificacoes.contains(e.target) && 
                    !this.btnNotificacoes.contains(e.target)) {
                    this.containerNotificacoes.classList.add('d-none');
                }
            });
        }
    }

    /**
     * Inicializa o gerenciador de notificações
     */
    async inicializar() {
        if (!this.containerNotificacoes || !this.contadorNotificacoes) {
            console.error('Elementos de notificação não encontrados');
            return;
        }

        await this.buscarNotificacoes();
        
        // Configura verificação periódica
        setInterval(() => this.buscarNotificacoes(), this.intervaloAtualizacao);
        
        // Configura eventos
        document.addEventListener('novoPrazo', () => this.buscarNotificacoes());
    }

    /**
     * Busca notificações pendentes do servidor
     */
    async buscarNotificacoes() {
        try {
            console.log('Buscando notificações...'); // Debug
            const response = await fetch(`${URL}/notificacoes/buscarPendentes`);
            if (!response.ok) throw new Error('Erro na requisição');
            
            const notificacoes = await response.json();
            console.log('Notificações:', notificacoes); // Debug
            this.atualizarInterface(notificacoes);
        } catch (erro) {
            console.error('Erro ao buscar notificações:', erro);
        }
    }

    /**
     * Atualiza a interface com as notificações
     */
    atualizarInterface(notificacoes) {
        if (!this.containerNotificacoes || !this.contadorNotificacoes) {
            console.error('Elementos não encontrados');
            return;
        }

        this.contadorNotificacoes.textContent = notificacoes.length;
        this.containerNotificacoes.innerHTML = '';

        if (notificacoes.length === 0) {
            this.containerNotificacoes.innerHTML = '<div class="p-3 text-center">Nenhuma notificação</div>';
            return;
        }

        notificacoes.forEach(notificacao => {
            this.containerNotificacoes.appendChild(this.criarElementoNotificacao(notificacao));
        });
    }

    /**
     * Cria o elemento HTML para uma notificação
     */
    criarElementoNotificacao(notificacao) {
        const div = document.createElement('div');
        div.className = 'notificacao-item';
        div.setAttribute('data-notificacao-id', notificacao.id);
        div.innerHTML = `
            <div class="notificacao-conteudo">
                <p>${notificacao.mensagem}</p>
                <small>${this.formatarData(notificacao.data_prazo)}</small>
            </div>
            <button onclick="gerenciadorNotificacoes.marcarComoLida(${notificacao.id})" class="btn-marcar-lida">
                <i class="fas fa-check"></i>
            </button>
        `;
        return div;
    }

    /**
     * Marca uma notificação como lida
     */
    async marcarComoLida(id) {
        try {
            const response = await fetch(`${URL}/notificacoes/marcarLida/${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            const resultado = await response.json();
            
            if (resultado.success) {
                // Remove a notificação da lista visualmente
                const elemento = document.querySelector(`[data-notificacao-id="${id}"]`);
                if (elemento) {
                    elemento.remove();
                    // Atualiza o contador
                    const contador = parseInt(this.contadorNotificacoes.textContent) - 1;
                    this.contadorNotificacoes.textContent = contador;
                    if (contador === 0) {
                        this.containerNotificacoes.innerHTML = '<p class="text-center">Nenhuma notificação pendente</p>';
                    }
                }
            }
        } catch (erro) {
            console.error('Erro ao marcar notificação como lida:', erro);
        }
    }

    /**
     * Formata a data para exibição
     */
    formatarData(data) {
        return new Date(data).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    /**
     * Verifica prazos manualmente
     */
    async verificarPrazos() {
        try {
            console.log('Verificando prazos...'); // Debug
            const response = await fetch(`${URL}/notificacoes/verificarPrazos`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const resultado = await response.json();
            
            if (resultado.success) {
                await this.buscarNotificacoes(); // Atualiza as notificações após verificar
                console.log('Prazos verificados:', resultado.message);
            }
        } catch (erro) {
            console.error('Erro ao verificar prazos:', erro);
        }
    }

    async atualizarListaNotificacoes(notificacoes) {
        if (!this.containerNotificacoes) return;

        if (notificacoes.length === 0) {
            this.containerNotificacoes.innerHTML = '<p class="text-center">Nenhuma notificação pendente</p>';
            this.contadorNotificacoes.textContent = '0';
            return;
        }

        const html = notificacoes.map(notificacao => `
            <div class="notificacao-item" data-notificacao-id="${notificacao.id}">
                <p class="mb-1">${notificacao.mensagem}</p>
                <small class="text-muted">Prazo: ${this.formatarData(notificacao.data_prazo)}</small>
                <button class="btn btn-sm btn-success marcar-lida" 
                        onclick="gerenciadorNotificacoes.marcarComoLida(${notificacao.id})">
                    Marcar como lida
                </button>
            </div>
        `).join('');

        this.containerNotificacoes.innerHTML = html;
        this.contadorNotificacoes.textContent = notificacoes.length;
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    window.gerenciadorNotificacoes = new GerenciadorNotificacoes();
    window.gerenciadorNotificacoes.inicializar().catch(console.error);
}); 