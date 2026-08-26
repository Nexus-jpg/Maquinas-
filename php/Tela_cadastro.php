<?php
session_start();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $senha   = $_POST['senha'] ?? '';

    $usuario_correto = "admin";
    $senha_correta   = "1234";

    if ($usuario === $usuario_correto && $senha === $senha_correta) {
        $_SESSION['usuario_logado'] = $usuario;
        header("Location: painel.php");
        exit;
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>

  <div class="card-container">
    <div class="logo-section">
      <img src="logo.png" alt="Sua Logo Aqui" class="logo-img">
    </div>

    <div class="divider"></div>

    <div class="form-section">
      <h2>CADASTRO</h2>

      <?php if (!empty($erro)): ?>
        <p style="color: #ff4d4d; font-size: 12px; text-align: center; margin-bottom: 15px; font-weight: bold;">
          <?php echo $erro; ?>
        </p>
      <?php endif; ?>

      <form action="" method="POST">
        <div class="input-group">
          <input type="text" name="usuario" placeholder="Nome do usuário..." required>
        </div>
        <div class="input-group">
          <input type="email" name="email" placeholder="Email..." required>
        </div>
        <div class="input-group">
          <input type="password" name="senha" placeholder="Senha..." required>
        </div>
        <button type="submit" class="btn-submit">Login</button>
      </form>
    </div>
  </div>

</body>
</html>
