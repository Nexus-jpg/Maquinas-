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
    <link rel="stylesheet" href="css/Tela_inicial.css">
    
</head>
<body>

<div class="titulo">
Bem vindo a nossa empresa.

</div>


 <div class="maquinarios_subtitulo">Nossos maquinarios</div>
  <img src="" alt="">
    
<form action="Tela_inicial.php" method="GET">
    <input type="text" name="busca" placeholder="Digite o maquinário...">
    <button type="submit">Buscar</button>
</form>

 



</body>
</html>
