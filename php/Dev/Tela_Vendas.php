<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Usuário/Login.php");
    exit;
}

if (!in_array($_SESSION['tipo_usuario'] ?? '', ['dev', 'gerente_dev'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../../conector/conexao.php';

$tipo = $_GET['tipo'] ?? 'data';

$titulo  = "";
$texto   = "";
$colunas = [];
$linhas  = [];

switch ($tipo) {

    case 'alugueis':
        $titulo  = "Aluguéis";
        $texto   = "Período de locação de cada pedido em andamento ou já finalizado:";
        $colunas = ['Produto', 'Cliente', 'Data início', 'Data fim'];

        $stmt = $pdo->query("SELECT m.nome AS produto, c.nome_completo AS cliente,
                                     p.data_inicio, p.data_fim
                              FROM pedidos p
                              INNER JOIN maquinarios m ON m.id_maquinario = p.maquinario_id
                              INNER JOIN Clientes c ON c.usuario_id = p.usuario_id
                              ORDER BY p.data_inicio DESC");
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'principais_clientes':
        $titulo  = "Principais clientes";
        $texto   = "Clientes que mais compram/alugam com a HeavyRent:";
        $colunas = ['Cliente', 'Qtd. de pedidos', 'Valor total'];

        $stmt = $pdo->query("SELECT c.nome_completo AS cliente, COUNT(p.Id_pedidos) AS qtd_pedidos,
                                     SUM(p.valor) AS valor_total
                              FROM pedidos p
                              INNER JOIN Clientes c ON c.usuario_id = p.usuario_id
                              GROUP BY c.usuario_id, c.nome_completo
                              ORDER BY valor_total DESC
                              LIMIT 20");
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'mais_vendidos':
        $titulo  = "Produtos mais vendidos";
        $texto   = "Maquinários com mais pedidos registrados:";
        $colunas = ['Produto', 'Qtd. de pedidos'];

        $stmt = $pdo->query("SELECT m.nome AS produto, COUNT(p.Id_pedidos) AS qtd_pedidos
                              FROM pedidos p
                              INNER JOIN maquinarios m ON m.id_maquinario = p.maquinario_id
                              GROUP BY m.id_maquinario, m.nome
                              ORDER BY qtd_pedidos DESC
                              LIMIT 20");
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'menos_vendidos':
        $titulo  = "Produtos menos vendidos";
        $texto   = "Maquinários com menos pedidos registrados (do menor pro maior):";
        $colunas = ['Produto', 'Qtd. de pedidos'];

        $stmt = $pdo->query("SELECT m.nome AS produto, COUNT(p.Id_pedidos) AS qtd_pedidos
                              FROM maquinarios m
                              LEFT JOIN pedidos p ON p.maquinario_id = m.id_maquinario
                              GROUP BY m.id_maquinario, m.nome
                              ORDER BY qtd_pedidos ASC
                              LIMIT 20");
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'data':
    default:
        $tipo    = 'data';
        $titulo  = "Data de vendas";
        $texto   = "Veja abaixo a data de cada pedido realizado:";
        $colunas = ['Produto', 'Cliente', 'Data do pedido', 'Valor'];

        $stmt = $pdo->query("SELECT m.nome AS produto, c.nome_completo AS cliente,
                                     p.data_pedido, p.valor
                              FROM pedidos p
                              INNER JOIN maquinarios m ON m.id_maquinario = p.maquinario_id
                              INNER JOIN Clientes c ON c.usuario_id = p.usuario_id
                              ORDER BY p.data_pedido DESC");
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeavyRent Dev - <?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="../../css/Dev_css/Tela_Vendas.css">
</head>
<body>

    <nav class="nav-minima">
        <a href="Tela_Dev.php">🏠 Página Inicial</a>
        <a href="Tela_Menu.php">☰ Menu</a>
    </nav>

    <main>
        <div class="conteudo">
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <p class="texto-intro"><?= htmlspecialchars($texto) ?></p>

            <div class="tabela-wrap">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($colunas as $coluna): ?>
                                <th><?= htmlspecialchars($coluna) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($linhas)): ?>
                            <tr>
                                <td colspan="<?= count($colunas) ?>" class="sem-dados">Nenhum dado encontrado.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($linhas as $linha): ?>
                            <tr>
                                <?php foreach ($linha as $valor): ?>
                                    <td><?= htmlspecialchars((string) $valor) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="rodape">
        <p>VOCÊ ESTÁ CONECTADO NA VERSÃO DEV</p>
    </footer>

    <script src="../../js/Dev_js/Tela_Vendas.js"></script>
</body>
</html>
