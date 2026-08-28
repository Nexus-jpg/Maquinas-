<?php


require_once '../conector/conexao.php';
$termo_busca = isset($_GET['busca']) ? $_GET['busca'] : '';

if (!empty($termo_busca)) {
    $sql = "SELECT * FROM maquinarios WHERE nome LIKE :busca OR categoria LIKE :busca";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':busca' => '%' . $termo_busca . '%']);
} 
else {
    $sql = "SELECT * FROM maquinarios";
    $stmt = $pdo->query($sql);
}

$maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HeavyRent - Início</title>
  <link rel="stylesheet" href="teste01.css">
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <header class="top-nav">
    <a href="Tela_inicial.php" class="nav-btn active">INÍCIO</a>
    <a href="Tela_produtos.php" class="nav-btn">CATÁLOGO</a>
    <a href="Tela_pedidos.php" class="nav-btn">PEDIDOS</a>
    <a href="#" class="nav-btn">AGENDAMENTO</a>
    <a href="#" class="nav-btn">SAIBA MAIS</a>
  </header>

  <main class="main-container">
    
    <h1 class="hero-title">
      O PESO DA<br>
      EXPERIÊNCIA A<br>
      SERVIÇO DA <span class="highlight">SUA</span><br>
      <span class="highlight">OBRA.</span>
    </h1>

    <div class="description-text">
      <p>
        Alugue guindastes, betoneiras e britadeiras com agendamento direto, 
        frota inspecionada e operadores certificados. Menos burocracia, 
        máxima produtividade na sua obra.
      </p>
    </div>

    <div class="logo-container">
      <img src="img/logo.png" alt="HeavyRent Logo" class="main-logo">
    </div>

  </main>

  <footer class="bottom-bar">
    <span class="footer-info">LOGÍSTICA SEGURA EM TODO O BRASIL</span>
    
    <div class="auth-buttons">
      <a href="Tela_cadastro.php" class="btn-login">LOGIN</a>
      <a href="Tela_cadastro.php" class="btn-register">CADASTRE-SE</a>
    </div>
  </footer>

</body>
</html>
