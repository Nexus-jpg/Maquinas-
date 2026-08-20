<?php




?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cadastro</title>
</head>
<body>
    <form action="/submit-data" method="POST">
  <div>
    <label for="username">usuario:</label>
    <input type="text" id="username" name="username" required>
  </div>
  
  <div>
    <label for="password">senha:</label>
    <input type="password" id="password" name="password" required>
  </div>
  
  <button type="submit">Logar</button>
</form>

</body>
</html>
