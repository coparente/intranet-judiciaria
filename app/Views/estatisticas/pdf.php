<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Estatísticas</title>
    <style>
        * {
            font-family: "Helvetica", sans-serif;
            box-sizing: border-box;
        }
        body {
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        .stats-card {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            background: #f9f9f9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="<?= $dados['logo_url'] ?>" alt="Logo" class="logo">
        <div class="title">Relatório de Estatísticas do Sistema</div>
        <div class="subtitle">Período: <?= $dados['filtros']['data_inicio'] ?? 'Início' ?> até <?= $dados['filtros']['data_fim'] ?? 'Atual' ?></div>
        <div class="subtitle">Gerado em: <?= $dados['data_geracao'] ?></div>
    </div>

    <h3>Estatísticas Gerais</h3>
    <div class="stats-card">
        <p>Total de Atividades: <?= $dados['estatisticas_gerais']['total_atividades'] ?></p>
        <p>Usuários Ativos Hoje: <?= $dados['estatisticas_gerais']['usuarios_ativos_hoje'] ?></p>
        <p>Média de Tempo por Sessão: <?= $dados['estatisticas_gerais']['media_tempo_sessao'] ?> minutos</p>
        <p>Tempo Total no Sistema: <?= number_format($dados['tempo_total_sistema'], 2) ?> minutos</p>
    </div>

    <?php if ($dados['estatisticas_usuario']): ?>
    <h3>Estatísticas do Usuário: <?= $dados['filtros']['nome_usuario'] ?></h3>
    <div class="stats-card">
        <p>Total de Dias Ativos: <?= $dados['estatisticas_usuario']->total_dias ?></p>
        <p>Média de Tempo por Dia: <?= number_format($dados['estatisticas_usuario']->media_minutos_dia, 2) ?> minutos</p>
        <p>Tempo Total de Uso: <?= number_format($dados['estatisticas_usuario']->total_minutos, 2) ?> minutos</p>
    </div>
    <?php endif; ?>

    <h3>Detalhamento por Usuário</h3>
    <table>
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Perfil</th>
                <th>Dias Ativos</th>
                <th>Primeira Atividade</th>
                <th>Última Atividade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados['tempo_sessao'] as $sessao): ?>
            <tr>
                <td><?= $sessao->nome ?></td>
                <td><?= ucfirst($sessao->perfil) ?></td>
                <td><?= $sessao->dias_ativos ?></td>
                <td><?= date('d/m/Y H:i', strtotime($sessao->primeira_atividade)) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($sessao->ultima_atividade)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Documento gerado automaticamente pelo Sistema em <?= date('d/m/Y H:i:s') ?></p>
    </div>
</body>
</html> 