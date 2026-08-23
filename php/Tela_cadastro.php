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
    <link rel="stylesheet" href="css/Tela_cadastro.css">
</head>

<body>
    <div class="formulario">

        <form action="action_page.php" method="post">


            <div class="usuario">

                <label for="Usuario_id">Usuário</label>
                <input type="text" id="Usuario_id" name="Usuario_id" placeholder="Nome do usuário" required>

            </div>
            <br>

            <div class="senha">

                <label for="senha_login">Senha</label>
                <input type="password" id="senha_login" name="senha_login" placeholder="Senha" required>


            </div>
            <br>
            <div class="buttom_login">
                <input type="submit" value="Confirmar">
            </div>



        </form>




</body>

</html>
