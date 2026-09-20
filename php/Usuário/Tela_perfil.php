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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'atualizar_dados') {
        $nome_completo = trim($_POST['nome_completo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');

        if (!empty($nome_completo) && !empty($email)) {
            try {
                $pdo->beginTransaction();

                $stmtUser = $pdo->prepare("UPDATE usuarios SET email = :email WHERE id = :id");
                $stmtUser->execute([':email' => $email, ':id' => $id_usuario_logado]);
                $stmtClienteCheck = $pdo->prepare("SELECT id FROM Clientes WHERE usuario_id = :usuario_id");
                $stmtClienteCheck->execute([':usuario_id' => $id_usuario_logado]);
                $clienteExiste = $stmtClienteCheck->fetch(PDO::FETCH_ASSOC);

                if ($clienteExiste) {
                    $stmtCli = $pdo->prepare("
                        UPDATE Clientes 
                        SET nome_completo = :nome, cpf_cnpj = :cpf, telefone = :telefone 
                        WHERE usuario_id = :usuario_id
                    ");
                } else {
                    $stmtCli = $pdo->prepare("
                        INSERT INTO Clientes (usuario_id, nome_completo, cpf_cnpj, telefone) 
                        VALUES (:usuario_id, :nome, :cpf, :telefone)
                    ");
                }

                $stmtCli->execute([
                    ':usuario_id' => $id_usuario_logado,
                    ':nome' => $nome_completo,
                    ':cpf' => $cpf_cnpj,
                    ':telefone' => $telefone
                ]);

                $pdo->commit();
                $mensagem_sucesso = "Perfil atualizado com sucesso!";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensagem_erro = "Erro ao atualizar perfil: " . $e->getMessage();
            }
        } else {
            $mensagem_erro = "Nome completo e E-mail são obrigatórios.";
        }
    } elseif ($_POST['acao'] === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        if (!empty($senha_atual) && !empty($nova_senha) && !empty($confirmar_senha)) {
            if ($nova_senha === $confirmar_senha) {
                try {
                    $stmtPass = $pdo->prepare("SELECT senha FROM usuarios WHERE id = :id");
                    $stmtPass->execute([':id' => $id_usuario_logado]);
                    $userPass = $stmtPass->fetch(PDO::FETCH_ASSOC);

                    $senhaValida = password_verify($senha_atual, $userPass['senha']) || ($senha_atual === $userPass['senha']);

                    if ($senhaValida) {
                        $novaSenhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $stmtUpdatePass = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
                        $stmtUpdatePass->execute([':senha' => $novaSenhaHash, ':id' => $id_usuario_logado]);

                        $mensagem_sucesso = "Senha alterada com sucesso!";
                    } else {
                        $mensagem_erro = "Senha atual incorreta.";
                    }
                } catch (PDOException $e) {
                    $mensagem_erro = "Erro ao alterar senha: " . $e->getMessage();
                }
            } else {
                $mensagem_erro = "A nova senha e a confirmação não coincidem.";
            }
        } else {
            $mensagem_erro = "Preencha todos os campos para alterar a senha.";
        }
    }
}

try {
    $stmtPerfil = $pdo->prepare("
        SELECT 
            u.id,
            u.usuario,
            u.email,
            u.tipo_usuario,
            u.criado_em,
            c.nome_completo,
            c.cpf_cnpj,
            c.telefone
        FROM usuarios u
        LEFT JOIN Clientes c ON c.usuario_id = u.id
        WHERE u.id = :id
    ");
    $stmtPerfil->execute([':id' => $id_usuario_logado]);
    $perfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);

    $stmtStats = $pdo->prepare("
        SELECT 
            COUNT(*) AS total_pedidos,
            SUM(CASE WHEN status IN ('concluido', 'entregue', 'FINALIZADO') THEN 1 ELSE 0 END) AS concluidos,
            SUM(CASE WHEN status IN ('pendente', 'em_andamento', 'confirmado', 'ATIVO') THEN 1 ELSE 0 END) AS ativos
        FROM pedidos
        WHERE usuario_id = :id
    ");
    $stmtStats->execute([':id' => $id_usuario_logado]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar perfil: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - Perfil do Usuário</title>
    <link rel="stylesheet" href="../../css/Usuário_css/Tela_perfil.css">
</head>
<body>

    <header class="header">
        <div class="nav-container">
            <a href="#" class="logo">MAQUINÁRIOS<span>.DB</span></a>
            <nav class="nav-menu" id="navMenu">
                <a href="#">INÍCIO</a>
                <a href="#">CATEGORIAS</a>
                <a href="#">PRODUTOS</a>
                <a href="Tela_pedidos.php">MEUS PEDIDOS</a>
                <a href="Tela_perfil.php" class="active">MINHA CONTA</a>
            </nav>
            <button class="hamburger" id="hamburgerBtn" aria-label="Abrir Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main class="main-container">
        <section class="profile-header-card">
            <div class="profile-avatar">
                <?= strtoupper(substr($perfil['usuario'], 0, 1)) ?>
            </div>
            <div class="profile-header-info">
                <h2><?= htmlspecialchars($perfil['nome_completo'] ?? $perfil['usuario']) ?></h2>
                <p class="profile-username">@<?= htmlspecialchars($perfil['usuario']) ?> • <span class="badge-role"><?= strtoupper($perfil['tipo_usuario']) ?></span></p>
                <p class="profile-joined">Membro desde: <?= date('d/m/Y', strtotime($perfil['criado_em'])) ?></p>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?= $stats['total_pedidos'] ?? 0 ?></span>
                <span class="stat-label">Total de Pedidos</span>
            </div>
            <div class="stat-card">
                <span class="stat-number color-active"><?= $stats['ativos'] ?? 0 ?></span>
                <span class="stat-label">Pedidos Ativos</span>
            </div>
            <div class="stat-card">
                <span class="stat-number color-done"><?= $stats['concluidos'] ?? 0 ?></span>
                <span class="stat-label">Pedidos Concluídos</span>
            </div>
        </section>

        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagem_sucesso) ?></div>
        <?php endif; ?>
        <?php if (!empty($mensagem_erro)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($mensagem_erro) ?></div>
        <?php endif; ?>

        <div class="profile-content-grid">
            <section class="card-form">
                <h3>Dados Pessoais</h3>
                <form action="Tela_perfil.php" method="POST">
                    <input type="hidden" name="acao" value="atualizar_dados">

                    <div class="form-group">
                        <label for="usuario">Nome de Usuário (login):</label>
                        <input type="text" id="usuario" value="<?= htmlspecialchars($perfil['usuario']) ?>" disabled class="input-disabled">
                    </div>

                    <div class="form-group">
                        <label for="nome_completo">Nome Completo:</label>
                        <input type="text" id="nome_completo" name="nome_completo" value="<?= htmlspecialchars($perfil['nome_completo'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($perfil['email']) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cpf_cnpj">CPF / CNPJ:</label>
                            <input type="text" id="cpf_cnpj" name="cpf_cnpj" value="<?= htmlspecialchars($perfil['cpf_cnpj'] ?? '') ?>" placeholder="000.000.000-00">
                        </div>

                        <div class="form-group">
                            <label for="telefone">Telefone / WhatsApp:</label>
                            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($perfil['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Salvar Alterações</button>
                </form>
            </section>
            <section class="card-form">
                <h3>Alterar Senha</h3>
                <form action="Tela_perfil.php" method="POST" id="formSenha">
                    <input type="hidden" name="acao" value="alterar_senha">

                    <div class="form-group">
                        <label for="senha_atual">Senha Atual:</label>
                        <input type="password" id="senha_atual" name="senha_atual" required>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">Nova Senha:</label>
                        <input type="password" id="nova_senha" name="nova_senha" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Nova Senha:</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                    </div>

                    <button type="submit" class="btn-secondary-warning">Atualizar Senha</button>
                </form>
            </section>
        </div>
    </main>

    <script src="../../js/Usuário_js/Tela_perfil.js"></script>
</body>
</html>
