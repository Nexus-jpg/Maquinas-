<?php
session_start();

require_once __DIR__ . '/../../conector/conexao.php';
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['id'])) {
    header('Location: Tela_login.php');
    exit;
}
$id_usuario_logado = $_SESSION['usuario_id'] ?? $_SESSION['id'];
$mensagem_sucesso = '';
$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'avaliar') {
    $pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
    $nota = filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_FLOAT);
    $comentario = trim($_POST['comentario'] ?? '');

    if ($pedido_id && $nota !== false && $nota >= 0 && $nota <= 5) {
        try {
            $stmtCheck = $pdo->prepare("
                SELECT Id_pedidos, status 
                FROM pedidos 
                WHERE Id_pedidos = :pedido_id AND usuario_id = :usuario_id
            ");
            $stmtCheck->execute([
                ':pedido_id' => $pedido_id,
                ':usuario_id' => $id_usuario_logado
            ]);
            $pedido = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($pedido) {
                $stmtAval = $pdo->prepare("
                    INSERT INTO avaliacoes (pedido_id, usuario_id, nota, comentario)
                    VALUES (:pedido_id, :usuario_id, :nota, :comentario)
                    ON DUPLICATE KEY UPDATE 
                        nota = VALUES(nota),
                        comentario = VALUES(comentario),
                        criado_em = CURRENT_TIMESTAMP
                ");
                $stmtAval->execute([
                    ':pedido_id' => $pedido_id,
                    ':usuario_id' => $id_usuario_logado,
                    ':nota' => $nota,
                    ':comentario' => $comentario
                ]);

                $mensagem_sucesso = "Avaliação salva com sucesso!";
            } else {
                $mensagem_erro = "Pedido inválido ou sem permissão para avaliar.";
            }
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao salvar avaliação: " . $e->getMessage();
        }
    } else {
        $mensagem_erro = "Por favor, selecione uma nota de 0 a 5 válida.";
    }
}

try {
    $queryPedidos = "
        SELECT 
            p.Id_pedidos,
            p.valor,
            p.data_inicio,
            p.data_fim,
            p.status,
            p.data_pedido,
            m.id_maquinario,
            m.nome AS maquinario_nome,
            m.descricao AS maquinario_descricao,
            m.imagem AS maquinario_imagem,
            m.valor_diaria,
            c.nome AS categoria_nome,
            
            -- Minha avaliação neste pedido (se houver)
            a_minha.nota AS minha_nota,
            a_minha.comentario AS meu_comentario,
            a_minha.criado_em AS minha_data_avaliacao,

            -- Média geral das avaliações deste maquinário
            (SELECT AVG(a_sub.nota) 
             FROM avaliacoes a_sub 
             JOIN pedidos p_sub ON a_sub.pedido_id = p_sub.Id_pedidos 
             WHERE p_sub.maquinario_id = m.id_maquinario) AS media_nota_maquinario,

            -- Total de avaliações deste maquinário
            (SELECT COUNT(a_sub.id_avaliacao) 
             FROM avaliacoes a_sub 
             JOIN pedidos p_sub ON a_sub.pedido_id = p_sub.Id_pedidos 
             WHERE p_sub.maquinario_id = m.id_maquinario) AS total_avaliacoes_maquinario

        FROM pedidos p
        INNER JOIN maquinarios m ON p.maquinario_id = m.id_maquinario
        LEFT JOIN categorias c ON m.id_categoria = c.id_categoria
        LEFT JOIN avaliacoes a_minha ON a_minha.pedido_id = p.Id_pedidos
        WHERE p.usuario_id = :usuario_id
        ORDER BY p.data_pedido DESC
    ";

    $stmtPedidos = $pdo->prepare($queryPedidos);
    $stmtPedidos->execute([':usuario_id' => $id_usuario_logado]);
    $pedidos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);

    $comentarios_outros = [];
    if (!empty($pedidos)) {
        $maquinario_ids = array_unique(array_column($pedidos, 'id_maquinario'));
        $inQuery = implode(',', array_map('intval', $maquinario_ids));

        if (!empty($inQuery)) {
            $sqlOutros = "
                SELECT 
                    a.id_avaliacao,
                    a.nota,
                    a.comentario,
                    a.criado_em,
                    p.maquinario_id,
                    u.usuario AS nome_usuario
                FROM avaliacoes a
                JOIN pedidos p ON a.pedido_id = p.Id_pedidos
                JOIN usuarios u ON a.usuario_id = u.id
                WHERE p.maquinario_id IN ($inQuery)
                ORDER BY a.criado_em DESC
            ";
            $stmtOutros = $pdo->query($sqlOutros);
            $todosComentarios = $stmtOutros->fetchAll(PDO::FETCH_ASSOC);

            foreach ($todosComentarios as $coment) {
                $comentarios_outros[$coment['maquinario_id']][] = $coment;
            }
        }
    }

} catch (PDOException $e) {
    die("Erro na busca de pedidos: " . $e->getMessage());
}

function renderEstrelas($nota) {
    $nota = floatval($nota);
    $html = '<div class="estrelas-display" title="Nota: ' . number_format($nota, 1) . '">';
    for ($i = 1; $i <= 5; $i++) {
        if ($nota >= $i) {
            $html .= '<span class="estrela cheia">&#9733;</span>';
        } elseif ($nota >= ($i - 0.5)) {
            $html .= '<span class="estrela metade">&#9733;</span>';
        } else {
            $html .= '<span class="estrela vazia">&#9734;</span>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Loja de Maquinários</title>
    <link rel="stylesheet" href="../../css/Usuário_css/Tela_pedidos.css">
</head>
<body>

    <header class="header">
        <div class="nav-container">
            <a href="#" class="logo">MAQUINÁRIOS<span>.DB</span></a>
            <nav class="nav-menu" id="navMenu">
                <a href="#">INÍCIO</a>
                <a href="#">CATEGORIAS</a>
                <a href="#">PRODUTOS</a>
                <a href="Tela_pedidos.php" class="active">MEUS PEDIDOS</a>
                <a href="Tela_perfil.php">MINHA CONTA</a>
            </nav>
            <button class="hamburger" id="hamburgerBtn" aria-label="Abrir Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <main class="main-container">
        <section class="filter-section">
            <div class="filter-tabs">
                <button class="tab-btn active" data-filter="todos">TODOS OS PEDIDOS</button>
                <button class="tab-btn" data-filter="em_andamento">EM ANDAMENTO</button>
                <button class="tab-btn" data-filter="entregue">ENTREGUES</button>
                <button class="tab-btn" data-filter="concluido">CONCLUÍDOS</button>
                <button class="tab-btn" data-filter="pendente">PENDENTES</button>
            </div>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar pedido ou máquina...">
            </div>
        </section>

        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagem_sucesso) ?></div>
        <?php endif; ?>
        <?php if (!empty($mensagem_erro)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($mensagem_erro) ?></div>
        <?php endif; ?>

        <section class="pedidos-list" id="pedidosList">
            <?php if (empty($pedidos)): ?>
                <div class="no-pedidos">
                    <p>Você ainda não realizou nenhum pedido no sistema.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pedidos as $p): 
                    $status_normalizado = strtolower($p['status']);
                    $imagem_path = !empty($p['maquinario_imagem']) ? htmlspecialchars($p['maquinario_imagem']) : '../../assets/placeholder-maquina.jpg';
                    $media_nota = $p['media_nota_maquinario'] ? round($p['media_nota_maquinario'], 1) : 0;
                    $total_avaliacoes = $p['total_avaliacoes_maquinario'] ?? 0;
                    $outros_coments = $comentarios_outros[$p['id_maquinario']] ?? [];
                ?>
                    <article class="card-pedido" data-status="<?= htmlspecialchars($status_normalizado) ?>" data-nome="<?= htmlspecialchars(strtolower($p['maquinario_nome'])) ?>">
              <div class="card-header">
                 <span class="pedido-id">PEDIDO #<?= $p['Id_pedidos'] ?></span>
                 <span class="pedido-data">Realizado em: <?= date('d/m/Y H:i', strtotime($p['data_pedido'])) ?></span>
                 <span class="badge-status status-<?= $status_normalizado ?>">
                    <?= strtoupper(str_replace('_', ' ', $p['status'])) ?>
                 </span>
                        </div>

                        <div class="card-body">
                            <div class="product-img-wrapper">
                                <img src="<?= $imagem_path ?>" alt="<?= htmlspecialchars($p['maquinario_nome']) ?>" class="product-img">
                            </div>

                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($p['maquinario_nome']) ?></h3>
                                <p class="product-desc"><?= htmlspecialchars(mb_strimwidth($p['maquinario_descricao'] ?? 'Sem descrição', 0, 140, '...')) ?></p>

                                <div class="período-aluguel">
                                    <span>Período: <strong><?= date('d/m/Y', strtotime($p['data_inicio'])) ?></strong> até <strong><?= date('d/m/Y', strtotime($p['data_fim'])) ?></strong></span>
                                </div>

                                <div class="rating-general">
                                    <span class="rating-number"><?= $media_nota > 0 ? number_format($media_nota, 1) : 'Sem notas' ?></span>
                                    <?= renderEstrelas($media_nota) ?>
                                    <span class="total-reviews">(<?= $total_avaliacoes ?> avaliações da máquina)</span>
                                </div>
                            </div>

                            <div class="product-price-box">
                                <span class="price-label">Valor Total</span>
                                <span class="price-value">R$ <?= number_format($p['valor'], 2, ',', '.') ?></span>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="my-review-box">
                                <h4>Sua Avaliação para este Pedido</h4>
                                <?php if (!empty($p['minha_nota'])): ?>
                                    <div class="review-exists">
                                        <div class="stars-user">
                                            <?= renderEstrelas($p['minha_nota']) ?>
                                            <span>(Sua nota: <?= number_format($p['minha_nota'], 1) ?>)</span>
                                        </div>
                                        <p class="user-comment-text">"<?= htmlspecialchars($p['meu_comentario']) ?>"</p>
                                        <button class="btn-secondary btn-editar-aval" 
                                                data-pedido="<?= $p['Id_pedidos'] ?>"
                                                data-nota="<?= $p['minha_nota'] ?>"
                                                data-comentario="<?= htmlspecialchars($p['meu_comentario']) ?>">
                                            Editar minha avaliação
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <?php if (in_array($status_normalizado, ['concluido', 'entregue', 'finalizado'])): ?>
                                        <button class="btn-primary btn-avaliar" data-pedido="<?= $p['Id_pedidos'] ?>">
                                            Avaliar este Pedido
                                        </button>
                                    <?php else: ?>
                                        <p class="review-disabled-msg">Você poderá avaliar este pedido assim que ele for entregue ou concluído.</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <button class="btn-toggle-comments" data-target="comments-<?= $p['Id_pedidos'] ?>">
                                Comentários de outros clientes (<?= count($outros_coments) ?>) &#9660;
                            </button>
                        </div>

                        <div class="comments-section hidden" id="comments-<?= $p['Id_pedidos'] ?>">
                            <h4 class="comments-title">O que outras pessoas disseram sobre <?= htmlspecialchars($p['maquinario_nome']) ?>:</h4>
                            <?php if (empty($outros_coments)): ?>
                                <p class="no-comments">Nenhum outro cliente comentou sobre esta máquina ainda.</p>
                            <?php else: ?>
                                <div class="comments-list">
                                    <?php foreach ($outros_coments as $c): ?>
                                        <div class="comment-item">
                                            <div class="comment-header">
                                                <div class="comment-avatar">
                                                    <?= strtoupper(substr($c['nome_usuario'], 0, 1)) ?>
                                                </div>
                                                <div class="comment-meta">
                                                    <strong><?= htmlspecialchars($c['nome_usuario']) ?></strong>
                        <span class="comment-date"><?= date('d/m/Y', strtotime($c['criado_em'])) ?></span>
                 </div>
             <div class="comment-stars">
                  <?= renderEstrelas($c['nota']) ?>
                  </div>
       </div>
           <p class="comment-body"><?= htmlspecialchars($c['comentario']) ?></p>
                 </div>
                 <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal-overlay hidden" id="modalAvaliacao">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">&times;</button>
            <h3>Avaliar Pedido</h3>
            <form action="Tela_pedidos.php" method="POST" id="formAvaliacao">
                <input type="hidden" name="acao" value="avaliar">
                <input type="hidden" name="pedido_id" id="modalPedidoId" value="">

                <div class="form-group">
                    <label>Sua Nota (1 a 5 estrelas):</label>
                    <div class="star-rating-input" id="starInputGroup">
                        <span class="star-btn" data-value="1">&#9733;</span>
                        <span class="star-btn" data-value="2">&#9733;</span>
                        <span class="star-btn" data-value="3">&#9733;</span>
                        <span class="star-btn" data-value="4">&#9733;</span>
                        <span class="star-btn" data-value="5">&#9733;</span>
                    </div>
                    <input type="hidden" name="nota" id="modalNota" value="5" required>
                </div>

                <div class="form-group">
                    <label for="modalComentario">Seu Comentário / Experiência:</label>
                    <textarea name="comentario" id="modalComentario" rows="4" placeholder="Escreva o que achou do equipamento, do processo de entrega ou do atendimento..." required></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="btnCancelModal">Cancelar</button>
                    <button type="submit" class="btn-primary">Enviar Avaliação</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../js/Usuário_js/Tela_pedidos.js"></script>
</body>
</html>
