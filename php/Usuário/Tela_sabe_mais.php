<?php
$mensagem_status = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $sobrenome = htmlspecialchars($_POST['sobrenome'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $mensagem = htmlspecialchars($_POST['mensagem'] ?? '');
    $mensagem_status = "Formulário enviado com sucesso!";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heavy Rent - Maquinários</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <nav class="nav-links">
            <a href="#" class="btn-nav">INÍCIO</a>
            <a href="#" class="btn-nav">CATÁLOGO</a>
            <a href="#" class="btn-nav">PEDIDOS</a>
            <a href="#" class="btn-nav">AGENDAMENTO</a>
            <a href="#" class="btn-nav">SAIBA MAIS</a>
        </nav>
        <div class="logo-container">
  <img src="logo.png" alt="Logo Heavy Rent" class="logo">
 </div>

    </header>
    <main class="main-container">
 <div class="card-outer">
 <div class="card-header"></div>
 <div class="card-inner">
 <?php if (!empty($mensagem_status)): ?>
    <p class="status-success"><?php echo $mensagem_status; ?></p>
 <?php endif; ?>

 <form id="contactForm" action="index.php" method="POST">
      <div class="form-group">
    <label for="nome">Nome</label>
     <input type="text" id="nome" name="nome" placeholder="Value" required>
 </div>
   <div class="form-group">
          <label for="sobrenome">sobrenome</label>
            <input type="text" id="sobrenome" name="sobrenome" placeholder="Value" required>
    </div>
<div class="form-group">
         <label for="email">Email</label>
           <input type="email" id="email" name="email" placeholder="Value" required>
                    </div>
   <div class="form-group">
           <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" rows="4" placeholder="Value" required></textarea>
     </div>
      <button type="submit" class="btn-submit">Submit</button>
    </form>

       </div>
  </div>
 </main>
 <script src="script.js"></script>

</body>
</html>
