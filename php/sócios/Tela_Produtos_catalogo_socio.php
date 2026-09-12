<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Usuário/Login.php");
    exit;
}

if (($_SESSION['tipo_usuario'] ?? '') !== 'socio') {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../../conector/conexao.php';

$usuarioId    = $_SESSION['usuario_id'];
$mensagemOk   = "";
$mensagemErro = "";

$tiposContrato = [
    'financiamento'          => 'Financiar o projeto',
    'desconto_preferencial'  => 'Desconto + primeira mão nos lançamentos',
];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'solicitar') {
    $maquinarioId = $_POST['maquinario_id'] ?? '';
    $tipoContrato = $_POST['tipo_contrato'] ?? '';

    if ($maquinarioId === '' || !isset($tiposContrato[$tipoContrato])) {
        $mensagemErro = "Selecione um tipo de contrato válido.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contratos_socios (usuario_id, maquinario_id, tipo_contrato, status)
                                    VALUES (:usuario_id, :maquinario_id, :tipo_contrato, 'pendente')");
            $stmt->execute([
                ':usuario_id'    => $usuarioId,
                ':maquinario_id' => $maquinarioId,
                ':tipo_contrato' => $tipoContrato,
            ]);
            $mensagemOk = "Solicitação enviada! Assim que for aprovada, o produto aparece em Minhas Preferências.";
        } catch (PDOException $e) {
         
            $mensagemErro = "Você já solicitou esse produto anteriormente.";
        }
    }
}

)

$sql = "SELECT m.id_maquinario, m.nome, m.categoria, m.descricao, m.valor_diaria, m.status AS status_produto,
               c.status AS status_contrato, c.tipo_contrato
        FROM maquinarios m
        LEFT JOIN contratos_socios c
            ON c.maquinario_id = m.id_maquinario AND c.usuario_id = :usuario_id
        ORDER BY m.nome";

$stmt = $pdo->prepare($sql);
$stmt->execute([':usuario_id' => $usuarioId]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeavyRent Sócios - Catálogo</title>
    <link rel="stylesheet" href="../../css/sócios_css/Tela_Produtos_catalogo_socio.css">
</head>
<body>

    <header class="topbar">
        <nav class="menu">
            <a href="../../index.php" class="btn-menu">INICIO</a>
            <a href="../Usuário/Tela_catalogo_desk.php" class="btn-menu">CATALOGO</a>
            <a href="../Usuário/Tela_pedidos.php" class="btn-menu">PEDIDOS</a>
            <a href="../Usuário/Tela_agendamento.php" class="btn-menu">AGENDAMENTO</a>
            <a href="../Usuário/Tela_sabe_mais.php" class="btn-menu">SAIBA MAIS</a>
        </nav>
        <div class="logo-wrap">
            <img src="../../img/Logos/logo.png" alt="HeavyRent">
        </div>
    </header>

    <nav class="subnav-socio">
        <a href="Tela_Produtos_catalogo_socio.php" class="ativo">Catálogo de Parceria</a>
        <a href="Tela_Produtos_preferenciais.php">Minhas Preferências</a>
    </nav>

    <main>
        <div class="titulo-marca">
            <h1>ÁREA DO <span>SÓCIO</span></h1>
            <p class="slogan">SELECIONE OS PRODUTOS QUE VOCÊ QUER TER CONTRATO</p>
        </div>

        <div class="conteudo">
            <?php if ($mensagemOk): ?>
                <p class="alerta alerta-sucesso"><?= htmlspecialchars($mensagemOk) ?></p>
            <?php endif; ?>
            <?php if ($mensagemErro): ?>
                <p class="alerta alerta-erro"><?= htmlspecialchars($mensagemErro) ?></p>
            <?php endif; ?>

            <div class="grade-produtos">
                <?php foreach ($produtos as $produto): ?>
                    <div class="card-produto">
                        <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                        <p class="categoria"><?= htmlspecialchars($produto['categoria']) ?></p>
                        <p class="descricao"><?= htmlspecialchars($produto['descricao']) ?></p>
                        <p class="valor">R$ <?= number_format((float) $produto['valor_diaria'], 2, ',', '.') ?> /dia</p>

                        <?php if ($produto['status_contrato'] === null): ?>
                            <form method="POST" action="Tela_Produtos_catalogo_socio.php" class="form-solicitar">
                                <input type="hidden" name="acao" value="solicitar">
                                <input type="hidden" name="maquinario_id" value="<?= (int) $produto['id_maquinario'] ?>">

                                <select name="tipo_contrato" required>
                                    <option value="" disabled selected>Tipo de contrato...</option>
                                    <?php foreach ($tiposContrato as $valor => $nome): ?>
                                        <option value="<?= $valor ?>"><?= htmlspecialchars($nome) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" class="btn-solicitar">Solicitar parceria</button>
                            </form>
                        <?php else: ?>
                            <span class="tag-status tag-<?= htmlspecialchars($produto['status_contrato']) ?>">
                                <?php
                                    $rotulos = ['pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'recusado' => 'Recusado'];
                                    echo $rotulos[$produto['status_contrato']] ?? $produto['status_contrato'];
                                ?>
                                — <?= htmlspecialchars($tiposContrato[$produto['tipo_contrato']] ?? '') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="rodape">
        <p>LOGÍSTICA SEGURA EM TODO O BRASIL</p>
    </footer>

    <script src="../../js/sócios_js/Tela_Produtos_catalogo_socio.js"></script>
</body>
</html>
