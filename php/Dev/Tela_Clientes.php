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

$tipo = $_GET['tipo'] ?? 'dados';

$titulo   = "";
$texto    = "";
$colunas  = [];
$linhas   = [];
$avisoExtra = "";

switch ($tipo) {

    case 'principais':
        $titulo  = "Principais clientes";
        $texto   = "Clientes com mais pedidos realizados na HeavyRent:";
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

    case 'socios':
        $titulo  = "Sócios";
        $texto   = "Clientes cadastrados como sócios da empresa:";
        $colunas = ['Cliente', 'CPF/CNPJ', 'Telefone'];


        $avisoExtra = "Ainda não existe uma coluna para diferenciar sócios de clientes comuns. "
                    . "Adicione, por exemplo, uma coluna 'tipo_cliente' na tabela Clientes pra isso funcionar de verdade.";
        break;

    case 'dados':
    default:
        $tipo    = 'dados';
        $titulo  = "Dados de clientes";
        $texto   = "Lista completa de clientes cadastrados:";
        $colunas = ['Nome', 'CPF/CNPJ', 'Telefone', 'Usuário', 'Cadastrado em'];

        $stmt = $pdo->query("SELECT c.nome_completo, c.cpf_cnpj, c.telefone, u.usuario, u.criado_em
                              FROM Clientes c
                              INNER JOIN usuarios u ON u.id = c.usuario_id
                              ORDER BY c.nome_completo");
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
    <link rel="stylesheet" href="../../css/Dev_css/Tela_Clientes.css">
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

            <?php if ($avisoExtra): ?>
                <p class="alerta alerta-aviso"><?= htmlspecialchars($avisoExtra) ?></p>
            <?php else: ?>
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
            <?php endif; ?>
        </div>
    </main>

    <footer class="rodape">
        <p>VOCÊ ESTÁ CONECTADO NA VERSÃO DEV</p>
    </footer>

    <script src="../../js/Dev_js/Tela_Clientes.js"></script>
</body>
</html>
