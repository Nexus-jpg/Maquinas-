<?php
require_once '../conector/conexao.php';


try {
    $sql = "SELECT * FROM maquinarios";
    $stmt = $pdo->query($sql);
    $maquinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar maquinários: " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossos Maquinários</title>
    <link rel="stylesheet" href="css/Tela_agendamento.css">   
</head>
<body>
    <header class="head">
        <nav class="navbar">
          <a href="Tela_inicial.php">Início</a>
          <a href="Tela_produtos.php">Maquinários</a>
          <a href="Tela_pedidos.php">Pedidos</a>
          <a href="Tela_cadastro.php">Cadastro</a>
          <a href="Tela_sabe_mais">Sobre nós</a>
        </nav>
        <form action="" class="search-bar">
         <input type="text" placeholder="Pesquisa...">
         <button type="submit"></button>
       
        </form>

    </header>

    <main>
        <h1 style="text-align: center; margin-top: 20px;">Nossos Maquinários Disponíveis</h1>
        <div class="container-produtos">
            <?php if (count($maquinarios) > 0): ?>
                <?php foreach ($maquinarios as $item): ?>
                    <div class="card-maquinario">
                        <div>
                        
    <?php echo htmlspecialchars($item['imagem']); ?><?php echo htmlspecialchars($item['nome']); ?>
                            
    <h3><?php echo htmlspecialchars($item['nome']); ?></h3>
    <p><?php echo htmlspecialchars($item['descricao']); ?></p>
    <p><strong>Diária:</strong> R$ <?php echo number_format($item['valor_diaria'], 2, ',', '.'); ?></p>                         
    <p><strong>Status:</strong> 
    <span class="status <?php echo $item['status']; ?>">
    <?php echo ucfirst($item['status']); ?>
    </span>
        </p>
    </div>

 <?php if ($item['status'] === 'disponivel'): ?>
<a href="Tela_agendamento.php?id=<?php echo $item['id_maquinario']; ?>" class="btn-agendar">Agendar este</a>
    <?php else: ?>
    <span class="btn-indisponivel">Não disponível</span>
    <?php endif; ?>
                    
</div>
 <?php endforeach; ?>
   <?php else: ?>
      <p style="grid-column: 1/-1; text-align: center;">Nenhum maquinário cadastrado no momento.</p>
     <?php endif; ?>


     </div>
    </main>

    <script src="Tela_cadastro.js"></script>
    
</body>
</html>
