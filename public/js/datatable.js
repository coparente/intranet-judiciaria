$(document).ready(function() {
    $('#tabelaAtividades').DataTable({
        pageLength: 50,
        lengthChange: false,
        info: true,
        responsive: true,
        autoWidth: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: URL + '/atividades/getAtividades',
            type: 'POST',
            dataType: 'json',
            data: function(d) {
                console.log('Dados enviados:', d); // Debug
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('Erro no DataTable:', error);
                console.error('Status:', xhr.status);
                console.error('Resposta:', xhr.responseText);
                console.error('Erro detalhado:', thrown);
                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log('Resposta parseada:', response);
                } catch (e) {
                    console.error('Erro ao parsear resposta:', e);
                }
            }
        },
        columns: [
            { 
                data: 'data_hora',
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY HH:mm') : '';
                }
            },
            { data: 'usuario_nome' },
            // { 
            //     data: 'usuario_perfil',
            //     render: function(data) {
            //         return data ? `<span class="badge bg-${data == 'admin' ? 'danger' : 'primary'}">${data}</span>` : '';
            //     }
            // },
            // { data: 'usuario_perfil' },
            { data: 'acao' },
            { data: 'descricao' }
        ],
        language: {
            emptyTable: "Nenhum registro encontrado",
            info: "Mostrando _START_ até _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 até 0 de 0 registros",
            infoFiltered: "(Filtrados de _MAX_ registros)",
            infoThousands: ".",
            loadingRecords: "Carregando...",
            processing: "Processando...",
            zeroRecords: "Nenhum registro encontrado",
            search: "Pesquisar",
            paginate: {
                next: "Próximo",
                previous: "Anterior",
                first: "Primeiro",
                last: "Último"
            },
            lengthMenu: "Exibir _MENU_ resultados por página"
        },
        order: [[0, 'desc']],
        
    });
});
