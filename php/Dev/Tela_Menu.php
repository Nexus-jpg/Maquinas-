<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Usuário/Login.php"); 
    exit;
}

$tipoUsuario = $_SESSION['tipo_usuario'] ?? '';

if (!in_array($tipoUsuario, ['dev', 'gerente_dev'])) {
    header("Location: ../../index.php"); 
    exit;
}

require_once __DIR__ . '/../../conector/conexao.php';

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Dev';
$ehGerente   = ($tipoUsuario === 'gerente_dev');


$recursos = [
    'produtos' => 'Produtos',
    'vendas'   => 'Vendas',
    'clientes' => 'Clientes',
];

$tiposPorRecurso = [
    'produtos' => [
        'descricao' => 'Descrição de produtos',
        'defeito'   => 'Produtos com defeito',
        'data'      => 'Data de produtos (alugado/devolvido)',
    ],
    'vendas' => [
        'data'                => 'Data de vendas',
        'alugueis'            => 'Aluguéis',
        'principais_clientes' => 'Principais clientes (que mais compram)',
        'mais_vendidos'       => 'Produtos mais vendidos',
        'menos_vendidos'      => 'Produtos menos vendidos',
    ],
    'clientes' => [
        'principais' => 'Principais clientes',
        'socios'     => 'Sócios',
        'dados'      => 'Dados de clientes',
    ],
];


$arquivoPorRecurso = [
    'produtos' => 'Tela_Produtos_descrição.php',
    'vendas'   => 'Tela_Vendas.php',
    'clientes' => 'Tela_Clientes.php',
];

$paginasDiretas = [
    'Tela_Produtos_descrição.php' => 'Produtos - Informações',
    'Tela_Produtos_dev.php'       => 'Produtos - Gerenciar (adicionar/remover/atualizar)',
    'Tela_Vendas.php'             => 'Vendas - Informações',
    'Tela_Clientes.php'           => 'Clientes - Informações',
];

if ($ehGerente) {
    $paginasDiretas['Tela_Dev.php'] = 'Equipe Dev (gerenciar funcionários)';
}

$mensagemErro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'filtro') {
        $recurso = $_POST['recurso'] ?? '';
        $tipo    = $_POST['tipo'] ?? '';

        $tipoValido = isset($tiposPorRecurso[$recurso][$tipo]);

        if (isset($recursos[$recurso]) && $tipoValido) {
            $arquivo = $arquivoPorRecurso[$recurso];
            header("Location: " . $arquivo . "?tipo=" . urlencode($tipo));
            exit;
        }

        $mensagemErro = "Página ainda não disponível para essa combinação.";

    } elseif ($acao === 'pagina') {
        $pagina = $_POST['pagina'] ?? '';

        if (isset($paginasDiretas[$pagina])) {
            header("Location: " . $pagina);
            exit;
        }

        $mensagemErro = "Página ainda não disponível.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeavyRent Dev - Menu</title>
    <link rel="stylesheet" href="../../css/Dev_css/Tela_Menu.css">
</head>
<body>

    <p class="breadcrumb">Menu</p>

    <header class="topbar">
        <nav class="menu">
            <a href="Tela_Dev.php" class="btn-menu">INICIO</a>
            <a href="../Usuário/Tela_catalogo_desk.php" class="btn-menu">CATALOGO</a>
            <a href="../Usuário/Tela_pedidos.php" class="btn-menu">PEDIDOS</a>
            <a href="../Usuário/Tela_agendamento.php" class="btn-menu">AGENDAMENTO</a>
            <a href="../Usuário/Tela_sabe_mais.php" class="btn-menu">SAIBA MAIS</a>
        </nav>

        <div class="area-dev">
            <span class="avatar" title="<?= htmlspecialchars($nomeUsuario) ?>">
                <?= strtoupper(substr($nomeUsuario, 0, 1)) ?>
            </span>
            <a href="Tela_Dev.php" class="icone-bug" title="Página inicial Dev">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                    <path d="M20 8h-2.81a5.985 5.985 0 0 0-1.82-1.96L17 4.41 15.59 3l-2.17 2.17a6.02 6.02 0 0 0-2.83 0L8.41 3 7 4.41l1.62 1.63A5.985 5.985 0 0 0 6.81 8H4v2h2.09c-.05.33-.09.66-.09 1v1H4v2h2v1c0 .34.04.67.09 1H4v2h2.81c1.04 1.79 2.97 3 5.19 3s4.15-1.21 5.19-3H20v-2h-2.09c.05-.33.09-.66.09-1v-1h2v-2h-2v-1c0-.34-.04-.67-.09-1H20V8zm-6 8h-4v-2h4v2zm0-4h-4v-2h4v2z"/>
                </svg>
            </a>
        </div>
    </header>

    <main>
        <section class="card-menu">

            <h1 class="titulo-menu">MENU DEV</h1>
            <p class="subtitulo-menu">Acesse os recursos exclusivos para desenvolvedores</p>

            <?php if ($mensagemErro): ?>
                <p class="alerta alerta-erro"><?= htmlspecialchars($mensagemErro) ?></p>
            <?php endif; ?>

            <form method="POST" action="Tela_Menu.php" class="form-dev" id="formFiltro">
                <input type="hidden" name="acao" value="filtro">

                <p class="titulo-secao">Buscar por recurso</p>

                <label for="recurso">Recurso</label>
                <select id="recurso" name="recurso" required>
                    <option value="" disabled selected>Selecione...</option>
                    <?php foreach ($recursos as $valor => $nome): ?>
                        <option value="<?= htmlspecialchars($valor) ?>"><?= htmlspecialchars($nome) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="tipo">Tipo de informação</label>
                <select id="tipo" name="tipo" required disabled>
                    <option value="" disabled selected>Selecione um recurso primeiro...</option>
                </select>

                <button type="submit" class="btn-submit" id="btnFiltro" disabled>Acessar</button>
            </form>

            <div class="divisor"><span>OU</span></div>

            <form method="POST" action="Tela_Menu.php" class="form-dev" id="formPagina">
                <input type="hidden" name="acao" value="pagina">

                <p class="titulo-secao">Ir direto para uma página</p>

                <label for="pagina">Página</label>
                <select id="pagina" name="pagina" required>
                    <option value="" disabled selected>Selecione...</option>
                    <?php foreach ($paginasDiretas as $arquivo => $nome): ?>
                        <option value="<?= htmlspecialchars($arquivo) ?>"><?= htmlspecialchars($nome) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-submit" id="btnPagina" disabled>Ir para página</button>
            </form>

        </section>
    </main>

    <footer class="rodape">
        <p>VOCÊ ESTÁ CONECTADO NA VERSÃO DEV</p>
    </footer>

    <script>

        const tiposPorRecurso = <?= json_encode($tiposPorRecurso, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="../../js/Dev_js/Tela_Menu.js"></script>
</body>
</html>
