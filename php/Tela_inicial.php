<?php


require_once '../conector/conexao.php';




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
