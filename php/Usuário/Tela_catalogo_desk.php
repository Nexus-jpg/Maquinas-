<?php
session_start();
require_once __DIR__ . '/../../conector/conexao.php';
try {
    $stmtCat = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    
    $categoria_id = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT);

    $sql = "
        SELECT 
            m.*, 
            c.nome AS categoria_nome,
            (SELECT AVG(a.nota) FROM avaliacoes a JOIN pedidos p ON a.pedido_id = p.Id_pedidos WHERE p.maquinario_id = m.id_maquinario) AS media_nota
        FROM maquinarios m
        INNER JOIN categorias c ON m.id_categoria = c.id_categoria
    ";

    if ($categoria_id) {
        $sql .= " WHERE m.id_categoria = :categoria_id";
    }

    $sql .= " ORDER BY m.nome ASC";

    $stmt = $pdo->prepare($sql);
    if ($categoria_id) {
        $stmt->bindValue(':categoria_id', $categoria_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    $maquinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar catálogo: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - HeavyRent</title>
    <link rel="stylesheet" href="../../css/Usuário_css/Tela_catalogo.css">
</head>
<body>

    <header class="header">
        <div class="nav-container">
            <a href="#" class="logo">HEAVY<span>RENT</span></a>
            <nav class="nav-menu" id="navMenu">
                <a href="#">INÍCIO</a>
                <a href="Tela_catalogo.php" class="active">CATÁLOGO</a>
                <a href="Tela_pedidos.php">PEDIDOS</a>
                <a href="#">AGENDAMENTO</a>
                <a href="Tela_perfil.php">MINHA CONTA</a>
            </nav>
            <button class="hamburger" id="hamburgerBtn">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <main class="main-container">
        <section class="catalogo-header">
            <h1 class="catalogo-title">CATÁLOGO</h1>
            <div class="filter-wrapper">
                <select id="filterCategoria" onchange="location = this.value;">
                    <option value="Tela_catalogo.php">Todas as Categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="Tela_catalogo.php?categoria=<?= $cat['id_categoria'] ?>" <?= $categoria_id == $cat['id_categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </section>

        <section class="catalogo-grid">
            <?php if (empty($maquinarios)): ?>
                <p class="no-products">Nenhum maquinário encontrado nesta categoria.</p>
            <?php else: ?>
                <?php foreach ($maquinarios as $m): 
                    $img = !empty($m['imagem']) ? htmlspecialchars($m['imagem']) : '../../assets/placeholder-maquina.jpg';
                    $status_class = strtolower($m['status']) === 'disponivel' || strtolower($m['status']) === 'disponível' ? 'badge-disp' : 'badge-indisp';
                ?>
                    <div class="card-maquina">
                        <div class="card-img-container">
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($m['nome']) ?>">
                        </div>

                        <div class="card-content">
                            <div class="card-badges">
                                <span class="badge-cat"><?= strtoupper(htmlspecialchars($m['categoria_nome'])) ?></span>
                                <span class="badge-status <?= $status_class ?>">• <?= strtoupper($m['status']) ?></span>
                            </div>

                            <h3 class="maquina-nome"><?= htmlspecialchars($m['nome']) ?></h3>

                            <p class="maquina-desc"><?= htmlspecialchars(mb_strimwidth($m['descricao'] ?? '', 0, 90, '...')) ?></p>

                            <div class="card-price-row">
                                <div class="price-box">
                                    <span class="price-val">R$ <?= number_format($m['valor_diaria'], 2, ',', '.') ?></span>
                                    <span class="price-period">/ dia</span>
                                </div>
                                <a href="Tela_fechamento.php?maquinario_id=<?= $m['id_maquinario'] ?>" class="btn-agendar">Alugar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>
