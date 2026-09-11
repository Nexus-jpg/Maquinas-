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

$mensagemOk   = "";
$mensagemErro = "";

$statusPermitidos = ['disponivel', 'alugado', 'manutencao'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar') {
    $nome        = trim($_POST['nome'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');
    $categoria   = trim($_POST['categoria'] ?? '');
    $valorDiaria = $_POST['valor_diaria'] ?? '';
    $status      = $_POST['status'] ?? 'disponivel';

    if ($nome === '' || $valorDiaria === '' || !in_array($status, $statusPermitidos)) {
        $mensagemErro = "Preencha nome, valor da diária e um status válido.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO maquinarios (nome, descricao, categoria, valor_diaria, status)
                                    VALUES (:nome, :descricao, :categoria, :valor_diaria, :status)");
            $stmt->execute([
                ':nome'         => $nome,
                ':descricao'    => $descricao,
                ':categoria'    => $categoria,
                ':valor_diaria' => $valorDiaria,
                ':status'       => $status,
            ]);
            $mensagemOk = "Produto adicionado com sucesso.";
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao adicionar produto.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    $id          = $_POST['id_maquinario'] ?? '';
    $nome        = trim($_POST['nome'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');
    $categoria   = trim($_POST['categoria'] ?? '');
    $valorDiaria = $_POST['valor_diaria'] ?? '';
    $status      = $_POST['status'] ?? 'disponivel';

    if ($id === '' || $nome === '' || $valorDiaria === '' || !in_array($status, $statusPermitidos)) {
        $mensagemErro = "Dados inválidos para atualização.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE maquinarios
                                    SET nome = :nome, descricao = :descricao, categoria = :categoria,
                                        valor_diaria = :valor_diaria, status = :status
                                    WHERE id_maquinario = :id");
            $stmt->execute([
                ':nome'         => $nome,
                ':descricao'    => $descricao,
                ':categoria'    => $categoria,
                ':valor_diaria' => $valorDiaria,
                ':status'       => $status,
                ':id'           => $id,
            ]);
            $mensagemOk = "Produto atualizado com sucesso.";
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao atualizar produto.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'remover') {
    $id = $_POST['id_maquinario'] ?? '';

    try {
        $stmt = $pdo->prepare("DELETE FROM maquinarios WHERE id_maquinario = :id");
        $stmt->execute([':id' => $id]);
        $mensagemOk = "Produto removido.";
    } catch (PDOException $e) {
        $mensagemErro = "Erro ao remover produto. Verifique se ele não está vinculado a algum pedido.";
    }
}

$produtos = [];
try {
    $produtos = $pdo->query("SELECT id_maquinario, nome, descricao, categoria, valor_diaria, status
                              FROM maquinarios
                              ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagemErro = $mensagemErro ?: "Não foi possível carregar os produtos.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeavyRent Dev - Gerenciar Produtos</title>
    <link rel="stylesheet" href="../../css/Dev_css/Tela_Produtos_dev.css">
</head>
<body>

    <nav class="nav-minima">
        <a href="Tela_Dev.php">🏠 Página Inicial</a>
        <a href="Tela_Menu.php">☰ Menu</a>
    </nav>

    <main>
        <div class="conteudo">
            <h1>Gerenciar produtos</h1>
            <p class="texto-intro">Adicione, atualize ou remova maquinários do catálogo.</p>

            <?php if ($mensagemOk): ?>
                <p class="alerta alerta-sucesso"><?= htmlspecialchars($mensagemOk) ?></p>
            <?php endif; ?>
            <?php if ($mensagemErro): ?>
                <p class="alerta alerta-erro"><?= htmlspecialchars($mensagemErro) ?></p>
            <?php endif; ?>

            <div class="painel">
                <h2>Adicionar novo produto</h2>
                <form method="POST" action="Tela_Produtos_dev.php" class="form-produto">
                    <input type="hidden" name="acao" value="adicionar">

                    <div class="linha-form">
                        <div class="campo">
                            <label>Nome</label>
                            <input type="text" name="nome" required>
                        </div>
                        <div class="campo">
                            <label>Categoria</label>
                            <input type="text" name="categoria" placeholder="Ex: Escavadeira">
                        </div>
                    </div>

                    <div class="campo">
                        <label>Descrição</label>
                        <textarea name="descricao" rows="3"></textarea>
                    </div>

                    <div class="linha-form">
                        <div class="campo">
                            <label>Valor diária (R$)</label>
                            <input type="number" name="valor_diaria" step="0.01" min="0" required>
                        </div>
                        <div class="campo">
                            <label>Status</label>
                            <select name="status">
                                <option value="disponivel">Disponível</option>
                                <option value="alugado">Alugado</option>
                                <option value="manutencao">Manutenção</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Adicionar produto</button>
                </form>
            </div>

            <div class="painel">
                <h2>Produtos cadastrados</h2>

                <div class="lista-produtos">
                    <div class="linha-cabecalho">
                        <span>Nome</span>
                        <span>Categoria</span>
                        <span>Valor diária</span>
                        <span>Status</span>
                        <span></span>
                    </div>

                    <?php if (empty($produtos)): ?>
                        <p class="sem-dados">Nenhum produto cadastrado.</p>
                    <?php endif; ?>

                    <?php foreach ($produtos as $produto): ?>
                        <div class="linha-produto">
                            <form method="POST" action="Tela_Produtos_dev.php" class="form-atualizar" id="form-<?= (int) $produto['id_maquinario'] ?>">
                            
                                <input type="hidden" name="acao" value="atualizar">
                                <input type="hidden" name="id_maquinario" value="<?= (int) $produto['id_maquinario'] ?>">
                                <input type="hidden" name="descricao" value="<?= htmlspecialchars($produto['descricao']) ?>">

                                <input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required>
                                <input type="text" name="categoria" value="<?= htmlspecialchars($produto['categoria']) ?>">
                                <input type="number" name="valor_diaria" step="0.01" value="<?= htmlspecialchars($produto['valor_diaria']) ?>" required>

                                <select name="status">
                                    <?php foreach ($statusPermitidos as $st): ?>
                                        <option value="<?= $st ?>" <?= $produto['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <div class="acoes-produto">
                                <button type="submit" form="form-<?= (int) $produto['id_maquinario'] ?>" class="btn-salvar">Salvar</button>

                                <form method="POST" action="Tela_Produtos_dev.php" onsubmit="return confirm('Remover este produto?');" class="form-remover">
                                    <input type="hidden" name="acao" value="remover">
                                    <input type="hidden" name="id_maquinario" value="<?= (int) $produto['id_maquinario'] ?>">
                                    <button type="submit" class="btn-remover">Remover</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="rodape">
        <p>VOCÊ ESTÁ CONECTADO NA VERSÃO DEV</p>
    </footer>

    <script src="../../js/Dev_js/Tela_Produtos_dev.js"></script>
</body>
</html>
