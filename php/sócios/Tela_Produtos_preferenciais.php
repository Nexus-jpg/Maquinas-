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
    'financiamento'         => 'Financiar o projeto',
    'desconto_preferencial' => 'Desconto + primeira mão nos lançamentos',
];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cancelar') {
    $idContrato = $_POST['id_contrato'] ?? '';

    try {
        $stmt = $pdo->prepare("DELETE FROM contratos_socios
                                WHERE id_contrato = :id AND usuario_id = :usuario_id AND status = 'pendente'");
        $stmt->execute([':id' => $idContrato, ':usuario_id' => $usuarioId]);

        if ($stmt->rowCount() > 0) {
            $mensagemOk = "Solicitação cancelada.";
        } else {
            $mensagemErro = "Não foi possível cancelar (só dá pra cancelar pedidos pendentes).";
        }
    } catch (PDOException $e) {
        $mensagemErro = "Erro ao cancelar a solicitação.";
    }
}


$sql = "SELECT c.id_contrato, c.tipo_contrato, c.status, c.data_solicitacao, c.data_aprovacao,
               m.nome, m.categoria, m.valor_diaria
        FROM contratos_socios c
        INNER JOIN maquinarios m ON m.id_maquinario = c.maquinario_id
        WHERE c.usuario_id = :usuario_id
        ORDER BY FIELD(c.status, 'pendente', 'aprovado', 'recusado'), c.data_solicitacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':usuario_id' => $usuarioId]);
$contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeavyRent Sócios - Minhas Preferências</title>
    <link rel="stylesheet" href="../../css/sócios_css/Tela_Produtos_preferenciais.css">
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
        <a href="Tela_Produtos_catalogo_socio.php">Catálogo de Parceria</a>
        <a href="Tela_Produtos_preferenciais.php" class="ativo">Minhas Preferências</a>
    </nav>

    <main>
        <div class="titulo-marca">
            <h1>MINHAS <span>PREFERÊNCIAS</span></h1>
            <p class="slogan">PRODUTOS COM CONTRATO OU SOLICITAÇÃO DE SÓCIO</p>
        </div>

        <div class="conteudo">
            <?php if ($mensagemOk): ?>
                <p class="alerta alerta-sucesso"><?= htmlspecialchars($mensagemOk) ?></p>
            <?php endif; ?>
            <?php if ($mensagemErro): ?>
                <p class="alerta alerta-erro"><?= htmlspecialchars($mensagemErro) ?></p>
            <?php endif; ?>

            <?php if (empty($contratos)): ?>
                <div class="vazio">
                    <p>Você ainda não solicitou nenhum produto.</p>
                    <a href="Tela_Produtos_catalogo_socio.php" class="btn-ir-catalogo">Ver catálogo de parceria</a>
                </div>
            <?php else: ?>
                <div class="lista-contratos">
                    <?php foreach ($contratos as $contrato): ?>
                        <div class="card-contrato">
                            <div class="info-contrato">
                                <h3><?= htmlspecialchars($contrato['nome']) ?></h3>
                                <p class="categoria"><?= htmlspecialchars($contrato['categoria']) ?></p>
                                <p class="tipo-contrato"><?= htmlspecialchars($tiposContrato[$contrato['tipo_contrato']] ?? '') ?></p>
                                <p class="data">
                                    Solicitado em <?= date('d/m/Y', strtotime($contrato['data_solicitacao'])) ?>
                                    <?php if ($contrato['data_aprovacao']): ?>
                                        &middot; Aprovado em <?= date('d/m/Y', strtotime($contrato['data_aprovacao'])) ?>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <div class="acao-contrato">
                                <span class="tag-status tag-<?= htmlspecialchars($contrato['status']) ?>">
                                    <?php
                                        $rotulos = ['pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'recusado' => 'Recusado'];
                                        echo $rotulos[$contrato['status']] ?? $contrato['status'];
                                    ?>
                                </span>

                                <?php if ($contrato['status'] === 'pendente'): ?>
                                    <form method="POST" action="Tela_Produtos_preferenciais.php" onsubmit="return confirm('Cancelar essa solicitação?');">
                                        <input type="hidden" name="acao" value="cancelar">
                                        <input type="hidden" name="id_contrato" value="<?= (int) $contrato['id_contrato'] ?>">
                                        <button type="submit" class="btn-cancelar">Cancelar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="rodape">
        <p>LOGÍSTICA SEGURA EM TODO O BRASIL</p>
    </footer>

    <script src="../../js/sócios_js/Tela_Produtos_preferenciais.js"></script>
</body>
</html>
