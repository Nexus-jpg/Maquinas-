<?php
session_start();


if (!isset($_SESSION['usuario_id'])) {
    header("Location: Tela_login.php"); 
    exit;
}

require_once __DIR__ . '/../../conector/conexao.php';

$usuario_id   = $_SESSION['usuario_id'];
$mensagemOk   = "";
$mensagemErro = "";

$valorNome      = "";
$valorSobrenome = "";
$valorEmail     = $_SESSION['usuario_email'] ?? "";
$valorMensagem  = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $valorNome      = trim($_POST['nome'] ?? '');
    $valorSobrenome = trim($_POST['sobrenome'] ?? '');
    $valorEmail     = trim($_POST['email'] ?? '');
    $valorMensagem  = trim($_POST['mensagem'] ?? '');

    if ($valorNome === '' || $valorSobrenome === '' || $valorEmail === '' || $valorMensagem === '') {
        $mensagemErro = "Preencha todos os campos antes de enviar.";
    } elseif (!filter_var($valorEmail, FILTER_VALIDATE_EMAIL)) {
        $mensagemErro = "Digite um e-mail válido.";
    } else {
        try {
            $sql = "INSERT INTO chamados_suporte (usuario_id, nome, sobrenome, email, mensagem)
                    VALUES (:usuario_id, :nome, :sobrenome, :email, :mensagem)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':nome'       => $valorNome,
                ':sobrenome'  => $valorSobrenome,
                ':email'      => $valorEmail,
                ':mensagem'   => $valorMensagem,
            ]);

        

            $mensagemOk = "Sua mensagem foi enviada com sucesso! Nossa equipe vai te responder em breve.";

            $valorNome = $valorSobrenome = $valorMensagem = "";

        } catch (PDOException $e) {
            $mensagemErro = "Erro ao enviar sua mensagem. Tente novamente mais tarde.";

        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeavyRent - Suporte</title>
    <link rel="stylesheet" href="../../css/Usuário_css/Tela_Suporte.css">
</head>
<body>

    <header class="topbar">
        <nav class="menu">
            <a href="../../index.php" class="btn-menu">INICIO</a>
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
        <div class="titulo-marca">
            <h1>HEAVY<span>RENT</span></h1>
            <p class="slogan">COMPRA &amp; LOCAÇÃO DE MÁQUINAS</p>
        </div>

        <section class="card-suporte">

            <?php if ($mensagemOk): ?>
                <p class="alerta alerta-sucesso"><?= htmlspecialchars($mensagemOk) ?></p>
            <?php endif; ?>

            <?php if ($mensagemErro): ?>
                <p class="alerta alerta-erro"><?= htmlspecialchars($mensagemErro) ?></p>
            <?php endif; ?>

            <form id="formSuporte" method="POST" action="Tela_Suporte.php" novalidate>

                <label for="nome">Name</label>
                <input type="text" id="nome" name="nome" placeholder="Value" value="<?= htmlspecialchars($valorNome) ?>">

                <label for="sobrenome">Surname</label>
                <input type="text" id="sobrenome" name="sobrenome" placeholder="Value" value="<?= htmlspecialchars($valorSobrenome) ?>">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Value" value="<?= htmlspecialchars($valorEmail) ?>">

                <label for="mensagem">Message</label>
                <textarea id="mensagem" name="mensagem" placeholder="Value" rows="4"><?= htmlspecialchars($valorMensagem) ?></textarea>

                <button type="submit" class="btn-submit">Submit</button>
            </form>
        </section>
    </main>

    <footer class="rodape">
        <p>LOGÍSTICA SEGURA EM TODO O BRASIL</p>
    </footer>

    <script src="../../js/Usuário_js/Tela_Suporte.js"></script>
</body>
</html>
