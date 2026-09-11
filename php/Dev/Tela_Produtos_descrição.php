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

$tipo = $_GET['tipo'] ?? 'descricao';


$titulo = "";
$texto  = "";
$linhas = [];
$colunas = [];

switch ($tipo) {

    case 'defeito':
        $titulo = "Produtos com defeito";
        $texto  = "Os principais produtos com defeito neste último mês são:";
        $colunas = ['Nome', 'Categoria', 'Valor diária', 'Status'];


        $stmt = $pdo->query("SELECT nome, categoria, valor_diaria, status
                              FROM maquinarios
                              WHERE status = 'manutencao'
                              ORDER BY nome");
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'data':
        $titulo = "Datas de locação dos produtos";
        $texto  = "Veja abaixo quando cada produto foi alugado e a data de devolução prevista:";
        $colunas = ['Produto', 'Data início', 'Data fim', 'Valor'];

        $stmt = $pdo->query("SELECT m.nome, p.data_inicio, p.data_fim, p.valor
                              FROM pedidos p
                              INNER JOIN maquinarios m ON m.id_maquinario = p.maquinario_id
                              ORDER BY p.data_inicio DESC");
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'descricao':
    default:
        $tipo   = 'descricao';
        $titulo = "Descrição de produtos";
        $texto  = "Confira abaixo os maquinários disponíveis para venda e locação:";
        $colunas = ['Nome', 'Categoria', 'Descrição', 'Valor diária', 'Status'];

        $stmt = $pdo->query("SELECT nome, categoria, descricao, valor_diaria, status
                              FROM maquinarios
                              ORDER BY nome");
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
    <link rel="stylesheet" href="../../css/Dev_css/Tela_Produtos_descrição.css">
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

    <script src="../../js/Dev_js/Tela_Produtos_descrição.js"></script>
</body>
</html>
