<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Produtividade</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        h2 {
            color: #3498db;
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .resumo {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            width: 23%;
            text-align: center;
            border-radius: 5px;
        }
        .card h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .card p {
            margin: 5px 0 0;
            font-size: 18px;
            font-weight: bold;
            color: #3498db;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Relatório de Produtividade</h1>
    
    <div class="info">
        <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($dados['filtros']['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($dados['filtros']['data_fim'])) ?></p>
        <?php if (isset($dados['nome_usuario'])): ?>
            <p><strong>Usuário:</strong> <?= $dados['nome_usuario'] ?></p>
        <?php else: ?>
            <p><strong>Usuário:</strong> Todos os usuários</p>
        <?php endif; ?>
        <p><strong>Data de geração:</strong> <?= $dados['data_geracao'] ?></p>
    </div>
    
    <?php if (isset($dados['resumo_usuario'])): ?>
    <h2>Resumo do Usuário</h2>
    <div class="resumo">
        <div class="card">
            <h3>Total de Processos</h3>
            <p><?= $dados['resumo_usuario']->total_processos ?></p>
        </div>
        <div class="card">
            <h3>Processos Concluídos</h3>
            <p><?= $dados['resumo_usuario']->total_concluidos ?></p>
        </div>
        <div class="card">
            <h3>Média Dias p/ Conclusão</h3>
            <p><?= number_format($dados['resumo_usuario']->media_dias_conclusao, 1) ?></p>
        </div>
        <div class="card">
            <h3>Total Movimentações</h3>
            <p><?= $dados['resumo_usuario']->total_movimentacoes ?></p>
        </div>
    </div>
    
    <h2>Detalhamento por Data</h2>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Total Processos</th>
                <th>Concluídos</th>
                <th>Em Análise</th>
                <th>Em Intimação</th>
                <th>Em Diligência</th>
                <th>Movimentações</th>
                <th>Média Dias</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados['produtividade'] as $prod): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($prod->data)) ?></td>
                    <td><?= $prod->total_processos ?></td>
                    <td><?= $prod->concluidos ?></td>
                    <td><?= $prod->em_analise ?></td>
                    <td><?= $prod->em_intimacao ?></td>
                    <td><?= $prod->em_diligencia ?></td>
                    <td><?= $prod->total_movimentacoes ?></td>
                    <td><?= number_format($prod->media_dias_conclusao, 1) ?> dias</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    
    <?php if (isset($dados['produtividade_geral'])): ?>
    <h2>Resumo Geral</h2>
    <div class="resumo">
        <div class="card">
            <h3>Total de Processos</h3>
            <p><?= $dados['resumo_geral']->total_processos ?></p>
        </div>
        <div class="card">
            <h3>Processos Concluídos</h3>
            <p><?= $dados['resumo_geral']->total_concluidos ?></p>
        </div>
        <div class="card">
            <h3>Média Dias p/ Conclusão</h3>
            <p><?= number_format($dados['resumo_geral']->media_dias_conclusao, 1) ?></p>
        </div>
        <div class="card">
            <h3>Total Movimentações</h3>
            <p><?= $dados['resumo_geral']->total_movimentacoes ?></p>
        </div>
    </div>
    
    <h2>Comparativo por Usuário</h2>
    <table>
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Perfil</th>
                <th>Total Processos</th>
                <th>Concluídos</th>
                <th>Em Análise</th>
                <th>Em Intimação</th>
                <th>Movimentações</th>
                <th>Média Dias</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados['produtividade_geral'] as $prod): ?>
                <tr>
                    <td><?= $prod->nome_usuario ?></td>
                    <td><?= ucfirst($prod->perfil_usuario) ?></td>
                    <td><?= $prod->total_processos ?></td>
                    <td><?= $prod->total_concluidos ?></td>
                    <td><?= $prod->total_analise ?></td>
                    <td><?= $prod->total_intimacao ?></td>
                    <td><?= $prod->total_movimentacoes ?></td>
                    <td><?= number_format($prod->media_dias_conclusao, 1) ?> dias</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    
    <div class="footer">
        <p>Documento gerado automaticamente pelo Sistema em <?= $dados['data_geracao'] ?></p>
    </div>
</body>
</html> 