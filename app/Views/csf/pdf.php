<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Visita Técnica - <?= $dados['visita']->proad ?></title>
    <style>
        @import url("https://fonts.cdnfonts.com/css/tw-cen-mt-condensed");
        #footer { position: fixed; left: 0px; bottom: -60px; right: 0px; height: 80px; }
        #footer .page:after {content: counter(page);}
        body {font-family: "Tw Cen MT", sans-serif;}

        .marca {
            position: fixed;
            left: 50px;
            top: 100px;
            width: 80%;
            opacity: 0.1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 5px;
        }
        
        #cabeca {
            background-color: #CCC;
        }
        
        .card-title {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <table style="width: 100%; top: -110px;">
        <thead>
        <tr>
        <th style="text-align: center;">
            <img src="<?= $dados['logo_url'] ?>" width="100" height="100" style="float: left; margin-right: 20px;">
            <img src="<?= $dados['logo_150_anos'] ?>" width="100" height="100" style="float: right; margin-left: 20px;">
            <br>
            PODER JUDICIÁRIO<br>
            Tribunal de Justiça do Estado de Goiás <br>
            Comissão de Soluções Fundiárias <br><br>
        </th>
    </tr>
        </thead>
        <br>
        <tbody>
        <tr>
            <td style="padding: 20px; border: 2px solid #000; margin-left: 1.5cm;">
            <b><label for="processo">Processo:</label></b> <?= $dados['visita']->processo ?><br>
            <b><label for="comarca">Comarca:</label></b> <?= $dados['visita']->comarca ?><br>
            <b><label for="autor">Autor:</label></b> <?= $dados['visita']->autor ?><br>
            <b><label for="reu">Réu:</label></b> <?= $dados['visita']->reu ?><br>
            <b><label for="proad">Proad:</label></b> <?= $dados['visita']->proad ?><br>
            <b><label for="ocupação">Ocupação:</label></b> <?= $dados['visita']->nome_ocupacao ?><br>
            <b><label for="area ocupada">Área ocupada:</label></b> <?= $dados['visita']->area_ocupada ?><br>
            <b><label for="Energia">Energia Elétrica:</label></b> <?= $dados['visita']->energia_eletrica ?><br>
            <b><label for="agua">Água Tratada:</label></b> <?= $dados['visita']->agua_tratada ?><br>
            <b><label for="risco">Área de Risco:</label></b> <?= $dados['visita']->area_risco ?><br>
            <b><label for="Moradia">Moradia:</label></b> <?= $dados['visita']->moradia ?><br>
            </td>
        </tr>
    </tbody>
    </table>

    <img class="marca" src="<?= $dados['logo_url'] ?>">
    
    <table>
        <thead>
            <tbody>
                <tr>
                <div class="card-header">
        <h5 class="card-title text-white"><span class="description">Quantidade Famílias no processo -
                <?= $dados['estatisticas']['total_participantes'] ?> 
            </span></h5>
        <h5 class="card-title text-white"><span class="description">Quantidade Pessoas -
                <?= $dados['estatisticas']['total_pessoas'] ?>
            </span></h5>
        <h5 class="card-title text-white"><span class="description">Quantidade vulneráveis -
                <?= $dados['estatisticas']['total_vulneravel'] ?>
            </span></h5>
        <h5 class="card-title text-white"><span class="description">Quantidade Lote Vago -
                <?= $dados['estatisticas']['total_lote_vago'] ?>
            </span></h5>
        <h5 class="card-title text-white"><span class="description">Quantidade que recebe auxílio do governo -
                <?= $dados['estatisticas']['total_auxilio'] ?>
            </span></h5>
        <h5 class="card-title text-white"><span class="description">Quantidade de famílias que moram no local -
                <?= $dados['estatisticas']['total_mora_local'] ?>
            </span></h5>
    </div>
                </tr>
            </tbody>
        </thead>
    </table>

    <div id="footer" class="row">
        <hr style="margin-bottom: 0;">
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width:60%; font-size: 10px; text-align: left;">COMISSÃO DE SOLUÇÕES FUNDIÁRIAS Telefone: (62)3216-2623</td>
                <td style="width:40%; font-size: 10px; text-align: right;"><p class="page">Página </p></td>
            </tr>
        </table>
    </div>
    
    <div id="content" style="margin-top: 0;">
        <table style="width: 100%; table-layout: fixed; font-size:9px; text-transform: uppercase; border-collapse: collapse;">
            <thead>
                <tr id="cabeca" style="margin-left: 0px; background-color:#CCC;">
                    <th style="width:20%; border: 1px solid black;">Nome</th>
                    <th style="width:8%; border: 1px solid black;">Vulnerável</th>
                    <th style="width:50%; border: 1px solid black;">Relatório informativo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados['participantes'] as $participante): ?>
                <tr style="border: 1px solid black;">
                    <td style="width:20%; border: 1px solid black;"><?= $participante->nome ?></td>
                    <td style="width:8%; border: 1px solid black;"><?= $participante->vulneravel ?></td>
                    <td style="width:50%; border: 1px solid black;"><?= $participante->descricao ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <hr>
    <table>
        <thead>
            <tbody>
                <tr>
                    <td style="font-size: 10px; width:730px; text-align: right;"><b>Total Registros:<span> <?= count($dados['participantes']) ?></span> </td>
                </tr>
            </tbody>
        </thead>
    </table>
</body>
</html> 