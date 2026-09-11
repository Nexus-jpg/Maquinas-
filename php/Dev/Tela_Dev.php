<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Usuário/Login.php");
    exit;
}

if (($_SESSION['tipo_usuario'] ?? '') !== 'gerente_dev') {
    header("Location: Tela_Menu.php");
    exit;
}

require_once __DIR__ . '/../../conector/conexao.php';

$nomeUsuario  = $_SESSION['usuario_nome'] ?? 'Dev';
$mensagemOk   = "";
$mensagemErro = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar') {

    $usuarioId      = $_POST['usuario_id'] ?? '';
    $nome           = trim($_POST['nome_funcionario'] ?? '');
    $genero         = $_POST['genero'] ?? '';
    $nacionalidade  = trim($_POST['nacionalidade'] ?? '');
    $dataNascimento = $_POST['data_nascimento'] ?? '';
    $cargo          = trim($_POST['cargo'] ?? '');
    $salario        = $_POST['salario'] ?? '';
    $tempoEmpresa   = $_POST['tempo_empresa'] ?? '';

    if ($usuarioId === '' || $nome === '' || $nacionalidade === '' || $cargo === '' || $salario === '') {
        $mensagemErro = "Preencha todos os campos obrigatórios.";
    } else {
        try {
            $sql = "INSERT INTO funcionarios
                        (usuario_id, nome_funcionario, genero, nacionalidade, data_nascimento, cargo, salario, tempo_empresa)
                    VALUES
                        (:usuario_id, :nome, :genero, :nacionalidade, :data_nascimento, :cargo, :salario, :tempo_empresa)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':usuario_id'      => $usuarioId,
                ':nome'            => $nome,
                ':genero'          => $genero,
                ':nacionalidade'   => $nacionalidade,
                ':data_nascimento' => $dataNascimento,
                ':cargo'           => $cargo,
                ':salario'         => $salario,
                ':tempo_empresa'   => $tempoEmpresa,
            ]);

            $mensagemOk = "Funcionário adicionado com sucesso.";
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao adicionar funcionário. Verifique se esse usuário já não está cadastrado.";
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'remover') {
    $idFuncionario = $_POST['id_funcionario'] ?? '';

    try {
        $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id_funcionario = :id");
        $stmt->execute([':id' => $idFuncionario]);
        $mensagemOk = "Funcionário removido.";
    } catch (PDOException $e) {
        $mensagemErro = "Erro ao remover funcionário.";
    }
}


$equipe = [];
try {
    $sql = "SELECT f.id_funcionario, f.nome_funcionario, f.genero, f.nacionalidade,
                   f.data_nascimento, f.cargo, f.salario, f.tempo_empresa,
                   u.usuario, u.tipo_usuario
            FROM funcionarios f
            INNER JOIN usuarios u ON u.id = f.usuario_id
            WHERE u.tipo_usuario IN ('dev', 'gerente_dev')
            ORDER BY f.nome_funcionario";
    $equipe = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagemErro = $mensagemErro ?: "Não foi possível carregar a equipe.";
}

$usuariosSemCadastro = [];
try {
    $sql = "SELECT u.id, u.usuario
            FROM usuarios u
            LEFT JOIN funcionarios f ON f.usuario_id = u.id
            WHERE u.tipo_usuario IN ('dev', 'gerente_dev') AND f.id_funcionario IS NULL";
    $usuariosSemCadastro = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeavyRent Dev - Início</title>
    <link rel="stylesheet" href="../../css/Dev_css/Tela_Dev.css">
</head>
<body>

    <p class="breadcrumb">Início Dev</p>

    <header class="topbar">
        <nav class="menu">
            <a href="Tela_Dev.php" class="btn-menu">INICIO</a>
            <a href="../Usuário/Tela_catalogo_desk.php" class="btn-menu">CATALOGO</a>
            <a href="../Usuário/Tela_pedidos.php" class="btn-menu">PEDIDOS</a>
            <a href="../Usuário/Tela_agendamento.php" class="btn-menu">AGENDAMENTO</a>
            <a href="../Usuário/Tela_sabe_mais.php" class="btn-menu">SAIBA MAIS</a>
        </nav>

        <div class="area-dev">
            <span class="avatar" title="<?= htmlspecialchars($nomeUsuario) ?>">
                <?= strtoupper(substr($nomeUsuario, 0, 1)) ?>
            </span>
            <a href="Tela_Menu.php" class="icone-bug" title="Menu Dev">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                    <path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/>
                </svg>
            </a>
        </div>
    </header>

    <main>
        <div class="conteudo-dev">

            <h1 class="titulo-pagina">Equipe Dev</h1>
            <p class="subtitulo-pagina">Gerencie quem faz parte da equipe de desenvolvimento</p>

            <?php if ($mensagemOk): ?>
                <p class="alerta alerta-sucesso"><?= htmlspecialchars($mensagemOk) ?></p>
            <?php endif; ?>
            <?php if ($mensagemErro): ?>
                <p class="alerta alerta-erro"><?= htmlspecialchars($mensagemErro) ?></p>
            <?php endif; ?>

            <div class="painel">
                <h2 class="titulo-painel">Membros da equipe</h2>

                <div class="tabela-wrap">
                    <table class="tabela-equipe">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Usuário</th>
                                <th>Cargo</th>
                                <th>Nível</th>
                                <th>Nacionalidade</th>
                                <th>Salário</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($equipe)): ?>
                                <tr>
                                    <td colspan="7" class="sem-dados">Nenhum funcionário cadastrado ainda.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($equipe as $membro): ?>
                                <tr>
                                    <td><?= htmlspecialchars($membro['nome_funcionario']) ?></td>
                                    <td>@<?= htmlspecialchars($membro['usuario']) ?></td>
                                    <td><?= htmlspecialchars($membro['cargo']) ?></td>
                                    <td>
                                        <span class="tag-nivel tag-<?= htmlspecialchars($membro['tipo_usuario']) ?>">
                                            <?= $membro['tipo_usuario'] === 'gerente_dev' ? 'Gerente' : 'Dev' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($membro['nacionalidade']) ?></td>
                                    <td>R$ <?= number_format((float) $membro['salario'], 2, ',', '.') ?></td>
                                    <td>
                                        <form method="POST" action="Tela_Dev.php" onsubmit="return confirm('Remover este funcionário?');">
                                            <input type="hidden" name="acao" value="remover">
                                            <input type="hidden" name="id_funcionario" value="<?= (int) $membro['id_funcionario'] ?>">
                                            <button type="submit" class="btn-remover">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="painel">
                <h2 class="titulo-painel">Adicionar funcionário</h2>

                <form method="POST" action="Tela_Dev.php" class="form-funcionario">
                    <input type="hidden" name="acao" value="adicionar">

                    <div class="linha-form">
                        <div class="campo">
                            <label for="usuario_id">Usuário (login)</label>
                            <select id="usuario_id" name="usuario_id" required>
                                <option value="" disabled selected>Selecione...</option>
                                <?php foreach ($usuariosSemCadastro as $u): ?>
                                    <option value="<?= (int) $u['id'] ?>">@<?= htmlspecialchars($u['usuario']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="nome_funcionario">Nome completo</label>
                            <input type="text" id="nome_funcionario" name="nome_funcionario" required>
                        </div>
                    </div>

                    <div class="linha-form">
                        <div class="campo">
                            <label for="genero">Gênero</label>
                            <select id="genero" name="genero" required>
                                <option value="M">Masculino</option>
                                <option value="F">Feminino</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="nacionalidade">Nacionalidade</label>
                            <input type="text" id="nacionalidade" name="nacionalidade" required>
                        </div>
                    </div>

                    <div class="linha-form">
                        <div class="campo">
                            <label for="data_nascimento">Data de nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" required>
                        </div>

                        <div class="campo">
                            <label for="cargo">Cargo</label>
                            <input type="text" id="cargo" name="cargo" placeholder="Ex: Desenvolvedor Backend" required>
                        </div>
                    </div>

                    <div class="linha-form">
                        <div class="campo">
                            <label for="salario">Salário (R$)</label>
                            <input type="number" id="salario" name="salario" step="0.01" min="0" required>
                        </div>

                        <div class="campo">
                            <label for="tempo_empresa">Data de entrada na empresa</label>
                            <input type="date" id="tempo_empresa" name="tempo_empresa" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Adicionar à equipe</button>
                </form>
            </div>

        </div>
    </main>

    <footer class="rodape">
        <p>VOCÊ ESTÁ CONECTADO NA VERSÃO DEV</p>
    </footer>

    <script src="../../js/Dev_js/Tela_Dev.js"></script>
</body>
</html>
