<?php



session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $usuario = $_POST['Usuario_id'] ?? '';
    $senha   = $_POST['senha_login'] ?? '';

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


?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cadastro</title>
  <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/Tela_cadastro.css">
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
