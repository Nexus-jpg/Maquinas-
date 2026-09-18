<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit;
}

require_once __DIR__ . '/../../conector/conexao.php';

$usuarioId    = $_SESSION['usuario_id'];
$mensagemOk   = "";
$mensagemErro = "";

$statusAvaliaveis = ['entregue', 'concluido'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'avaliar') {
    $pedidoId  = $_POST['pedido_id'] ?? '';
    $nota      = $_POST['nota'] ?? '';
    $comentario = trim($_POST['comentario'] ?? '');

    $stmt = $pdo->prepare("SELECT status FROM pedidos WHERE Id_pedidos = :id AND usuario_id = :usuario_id");
    $stmt->execute([':id' => $pedidoId, ':usuario_id' => $usuarioId]);
    $pedidoDono = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedidoDono) {
        $mensagemErro = "Pedido não encontrado.";
    } elseif (!in_array($pedidoDono['status'], $statusAvaliaveis)) {
        $mensagemErro = "Esse pedido ainda não pode ser avaliado.";
    } elseif ($nota === '' || (float) $nota < 1 || (float) $nota > 5) {
        $mensagemErro = "Escolha uma nota de 1 a 5 estrelas.";
    } else {
        try {
            $sql = "INSERT INTO avaliacoes (pedido_id, usuario_id, nota, comentario)
                    VALUES (:pedido_id, :usuario_id, :nota, :comentario)
                    ON DUPLICATE KEY UPDATE nota = :nota2, comentario = :comentario2";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':pedido_id'    => $pedidoId,
                ':usuario_id'   => $usuarioId,
                ':nota'         => $nota,
                ':comentario'   => $comentario,
                ':nota2'        => $nota,
                ':comentario2'  => $comentario,
            ]);
            $mensagemOk = "Avaliação salva. Obrigado pelo feedback!";
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao salvar avaliação.";
        }
    }
}

$abasValidas = ['em_andamento', 'entregues', 'concluidos', 'avaliacoes'];
$aba = $_GET['aba'] ?? 'em_andamento';
if (!in_array($aba, $abasValidas)) {
    $aba = 'em_andamento';
}

$baseSql = "SELECT p.Id_pedidos, p.status, p.valor, p.data_inicio, p.data_fim, p.data_pedido,
                   m.nome AS produto_nome, m.descricao AS produto_descricao, m.imagem, m.categoria,
                   a.nota, a.comentario
            FROM pedidos p
            INNER JOIN maquinarios m ON m.id_maquinario = p.maquinario_id
            LEFT JOIN avaliacoes a ON a.pedido_id = p.Id_pedidos
            WHERE p.usuario_id = :usuario_id";

switch ($aba) {
    case 'entregues':
        $sql = $baseSql . " AND p.status = 'entregue' ORDER BY p.data_pedido DESC";
        break;
    case 'concluidos':
        $sql = $baseSql . " AND p.status = 'concluido' ORDER BY p.data_pedido DESC";
        break;
    case 'avaliacoes':
        $sql = $baseSql . " AND a.id_avaliacao IS NOT NULL ORDER BY a.criado_em DESC";
        break;
    case 'em_andamento':
    default:
        $sql = $baseSql . " AND p.status IN ('pendente', 'confirmado', 'em_andamento') ORDER BY p.data_pedido DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute([':usuario_id' => $usuarioId]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pedidoDetalhe = null;
$verId = $_GET['ver'] ?? null;

if ($verId) {
    $stmt = $pdo->prepare($baseSql . " AND p.Id_pedidos = :id");
    $stmt->execute([':usuario_id' => $usuarioId, ':id' => $verId]);
    $pedidoDetalhe = $stmt->fetch(PDO::FETCH_ASSOC);
}

$rotulosStatus = [
    'pendente'     => 'Pendente',
    'confirmado'   => 'Confirmado',
    'em_andamento' => 'Em andamento',
    'entregue'     => 'Entregue',
    'concluido'    => 'Concluído',
    'cancelado'    => 'Cancelado',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeavyRent - Meus Pedidos</title>
    <link rel="stylesheet" href="../../css/Usuário_css/Tela_pedidos.css">
</head>
<body>

    <p class="breadcrumb">Pedidos<?= $pedidoDetalhe ? '/' . $rotulosStatus[$pedidoDetalhe['status']] : '' ?></p>

    <header class="topbar">
        <nav class="menu">
            <a href="Tela_inicial.php" class="btn-menu">INICIO</a>
            <a href="Tela_catalogo_desk.php" class="btn-menu">CATALOGO</a>
            <a href="Tela_pedidos.php" class="btn-menu">PEDIDOS</a>
            <a href="Tela_agendamento.php" class="btn-menu">AGENDAMENTO</a>
            <a href="Tela_sabe_mais.php" class="btn-menu">SAIBA MAIS</a>
        </nav>
        <div class="logo-wrap">
            <img src="../../img/Logos/logo.png" alt="HeavyRent">
        </div>
    </header>

    <main>
        <nav class="abas-pedidos">
            <a href="?aba=entregues" class="<?= $aba === 'entregues' ? 'aba-ativa' : '' ?>">ENTREGUES</a>
            <a href="?aba=concluidos" class="<?= $aba === 'concluidos' ? 'aba-ativa' : '' ?>">CONCLUÍDOS</a>
            <a href="?aba=avaliacoes" class="<?= $aba === 'avaliacoes' ? 'aba-ativa' : '' ?>">AVALIAÇÕES</a>
            <a href="?aba=em_andamento" class="<?= $aba === 'em_andamento' ? 'aba-ativa' : '' ?>">EM ANDAMENTO</a>
            <button type="button" class="btn-filtro" id="btnFiltro" title="Ordenar">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M3 6h18M6 12h12M10 18h4"/>
                </svg>
            </button>
        </nav>
        <?php if ($mensagemOk): ?>
            <p class="alerta alerta-sucesso"><?= htmlspecialchars($mensagemOk) ?></p>
        <?php endif; ?>
        <?php if ($mensagemErro): ?>
            <p class="alerta alerta-erro"><?= htmlspecialchars($mensagemErro) ?></p>
        <?php endif; ?>
        <?php if ($pedidoDetalhe): ?>
            <a href="?aba=<?= htmlspecialchars($aba) ?>" class="link-voltar">&larr; Voltar para a lista</a>
            <section class="card-detalhe">

                <div class="detalhe-produto">
                    <img src="<?= $pedidoDetalhe['imagem'] ? '../../img/Maquinarios/' . htmlspecialchars($pedidoDetalhe['imagem']) : '../../img/Logos/logo.png' ?>"
                         alt="<?= htmlspecialchars($pedidoDetalhe['produto_nome']) ?>" class="img-produto">

                    <div class="mini-info">
                        <span class="mini-categoria"><?= htmlspecialchars($pedidoDetalhe['categoria']) ?></span>
                        <h3><?= htmlspecialchars($pedidoDetalhe['produto_nome']) ?></h3>
                        <p class="mini-valor">R$ <?= number_format((float) $pedidoDetalhe['valor'], 2, ',', '.') ?></p>
                    </div>
                </div>

                <div class="detalhe-info">
                    <p><strong>Status:</strong>
                        <span class="tag-status tag-<?= htmlspecialchars($pedidoDetalhe['status']) ?>">
                            <?= $rotulosStatus[$pedidoDetalhe['status']] ?>
                        </span>
                        <span class="detalhe-data"><?= date('d/m/Y', strtotime($pedidoDetalhe['data_pedido'])) ?></span>
                    </p>

                    <p class="detalhe-periodo">
                        Locação de <?= date('d/m/Y', strtotime($pedidoDetalhe['data_inicio'])) ?>
                        até <?= date('d/m/Y', strtotime($pedidoDetalhe['data_fim'])) ?>
                    </p>

                    <p class="detalhe-descricao">
                        <strong>Descrição:</strong> <?= htmlspecialchars($pedidoDetalhe['produto_descricao']) ?>
                    </p>

                    <?php if ($pedidoDetalhe['nota']): ?>
                        <div class="nota-exibida">
                            <span class="numero-nota"><?= number_format((float) $pedidoDetalhe['nota'], 1) ?></span>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="estrela <?= $i <= round($pedidoDetalhe['nota']) ? 'estrela-cheia' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>

                    <div class="secao-comentarios">
                        <h4>Comentários:</h4>

                        <?php if ($pedidoDetalhe['comentario']): ?>
                            <div class="comentario-existente">
                                <span class="avatar-comentario">🙂</span>
                                <p><?= nl2br(htmlspecialchars($pedidoDetalhe['comentario'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array($pedidoDetalhe['status'], $statusAvaliaveis)): ?>
                            <form method="POST" action="Tela_pedidos.php?aba=<?= htmlspecialchars($aba) ?>&ver=<?= (int) $pedidoDetalhe['Id_pedidos'] ?>" class="form-avaliar">
                                <input type="hidden" name="acao" value="avaliar">
                                <input type="hidden" name="pedido_id" value="<?= (int) $pedidoDetalhe['Id_pedidos'] ?>">

                                <div class="estrelas-input">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="nota" id="estrela<?= $i ?>" value="<?= $i ?>"
                                            <?= (int) $pedidoDetalhe['nota'] === $i ? 'checked' : '' ?>>
                                        <label for="estrela<?= $i ?>">★</label>
                                    <?php endfor; ?>
                                </div>
                                <textarea name="comentario" rows="2" placeholder="Escreva um comentário..."><?= htmlspecialchars($pedidoDetalhe['comentario'] ?? '') ?></textarea>
                                <button type="submit" class="btn-enviar-avaliacao">
                                    <?= $pedidoDetalhe['nota'] ? 'Atualizar avaliação' : 'Enviar avaliação' ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="aviso-sem-avaliacao">Esse pedido ainda não foi entregue, por isso não pode ser avaliado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

        <?php else: ?>

            <div class="grade-pedidos">
                <?php if (empty($pedidos)): ?>
                    <p class="sem-pedidos">Nenhum pedido encontrado nessa aba.</p>
                <?php endif; ?>

                <?php foreach ($pedidos as $pedido): ?>
                    <a href="?aba=<?= htmlspecialchars($aba) ?>&ver=<?= (int) $pedido['Id_pedidos'] ?>" class="card-pedido">
                        <img src="<?= $pedido['imagem'] ? '../../img/Maquinarios/' . htmlspecialchars($pedido['imagem']) : '../../img/Logos/logo.png' ?>"
                             alt="<?= htmlspecialchars($pedido['produto_nome']) ?>">

                        <div class="card-pedido-info">
                            <h3><?= htmlspecialchars($pedido['produto_nome']) ?></h3>
                            <span class="tag-status tag-<?= htmlspecialchars($pedido['status']) ?>">
                                <?= $rotulosStatus[$pedido['status']] ?>
                            </span>
                            <p class="card-pedido-data"><?= date('d/m/Y', strtotime($pedido['data_pedido'])) ?></p>
                            <p class="card-pedido-valor">R$ <?= number_format((float) $pedido['valor'], 2, ',', '.') ?></p>

                            <?php if ($pedido['nota']): ?>
                                <span class="card-pedido-nota">★ <?= number_format((float) $pedido['nota'], 1) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="rodape">
        <p>LOGÍSTICA SEGURA EM TODO O BRASIL</p>
    </footer>
    <script src="../../js/Usuário_js/Tela_pedidos.js"></script>
</body>
</html>
