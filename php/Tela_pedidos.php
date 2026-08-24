<?php
require_once '../conector/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id    = $_POST['usuario_id'];
    $maquinario_id = $_POST['maquinario_id'];
    $data_inicio   = $_POST['data_inicio'];
    $data_fim      = $_POST['data_fim'];
    $inicio = new DateTime($data_inicio);
    $fim    = new DateTime($data_fim);
    $diferenca = $inicio->diff($fim);
    $dias = $diferenca->days;
    
    if ($dias <= 0) {
        $dias = 1; 
    }

    $stmt = $pdo->prepare("SELECT valor_diaria FROM maquinarios WHERE id = :id");
    $stmt->execute([':id' => $maquinario_id]);
    $maquina = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($maquina) {
        $valor_total = $dias * $maquina['valor_diaria'];
        $sql = "INSERT INTO pedidos (usuario_id, maquinario_id, valor, data_inicio, data_fim) 
                VALUES (:usuario, :maquina, :valor, :inicio, :fim)";
        
        $stmt_insert = $pdo->prepare($sql);
        $stmt_insert->execute([
            ':usuario' => $usuario_id,
            ':maquina' => $maquinario_id,
            ':valor'   => $valor_total,
            ':inicio'  => $data_inicio,
            ':fim'     => $data_fim
        ]);

        echo "Pedido realizado com sucesso Total: R$ " . number_format($valor_total, 2, ',', '.');
    }
}
?>





<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/Tela_pedidos.css">
    
</head>
<body>
    <h1>Agendamento</h1>
    <h2>Pedidos:</h2>
   <header class="head">
        <nav class="navbar">

            <a href="Tela_inicial.php">Inicio</a>
            <a href="Tela_produtos.php">Maquinarios</a>
            <a href="Tela_pedidos.php">pedidos</a>
            <a href="Tela_cadastro.php">cadastro</a>
            <a href="">sobre nós</a>
        </nav>
        <form action="" class="search-bar">
            <input type="text" placeholder="Pesquisa...">
            <button type="submit">

            </button>
        </form>





    </header>
    
<form action="Tela_pedidos.php" method="POST">
    <input type="hidden" name="usuario_id" value="1">
    <input type="hidden" name="maquinario_id" value="1">

    <label>Data de Início:</label>
    <input type="date" name="data_inicio" required>

    <label>Data de Término:</label>
    <input type="date" name="data_fim" required>

    <button type="submit">Finalizar Pedido</button>
</form>

    <script src="Tela_cadastro.js"></script>

    
    
     


</body>
</html>
