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
    <title>primeira página</title>
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/Tela_inicial.css">
    
</head>
<body>

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


    <script src="Tela_cadastro.js"></script>

 



</body>
</html>
