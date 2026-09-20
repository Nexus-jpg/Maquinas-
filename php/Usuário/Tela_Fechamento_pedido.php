<?php
session_start();
require_once __DIR__ . '/../../conector/conexao.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['id'])) {
    header('Location: Tela_login.php');
    exit;
}

$id_usuario_logado = $_SESSION['usuario_id'] ?? $_SESSION['id'];
$maquinario_id = filter_input(INPUT_GET, 'maquinario_id', FILTER_VALIDATE_INT);

if (!$maquinario_id) {
    header('Location: Tela_catalogo.php');
    exit;
}

try {
    $stmtM = $pdo->prepare("SELECT m.*, c.nome as categoria_nome FROM maquinarios m JOIN categorias c ON m.id_categoria = c.id_categoria WHERE id_maquinario = :id");
    $stmtM->execute([':id' => $maquinario_id]);
    $maquina = $stmtM->fetch(PDO::FETCH_ASSOC);

    if (!$maquina) {
        die("Maquinário não encontrado.");
    }
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'finalizar_pedido') {
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    $forma_pagamento = $_POST['forma_pagamento'] ?? 'pix';

    $cep = $_POST['cep'] ?? '';
    $logradouro = $_POST['logradouro'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? 'SP';

    if ($data_inicio && $data_fim && strtotime($data_fim) >= strtotime($data_inicio)) {
        $d1 = new DateTime($data_inicio);
        $d2 = new DateTime($data_fim);
        $dias = $d1->diff($d2)->days + 1;
        $valor_total = $dias * $maquina['valor_diaria'];

        try {
            $pdo->beginTransaction();

            $stmtPed = $pdo->prepare("
                INSERT INTO pedidos (usuario_id, maquinario_id, valor, data_inicio, data_fim, status)
                VALUES (:usuario_id, :maquinario_id, :valor, :data_inicio, :data_fim, 'pendente')
            ");
            $stmtPed->execute([
                ':usuario_id' => $id_usuario_logado,
                ':maquinario_id' => $maquinario_id,
                ':valor' => $valor_total,
                ':data_inicio' => $data_inicio,
                ':data_fim' => $data_fim
            ]);
            $pedido_id = $pdo->lastInsertId();

            $stmtEnd = $pdo->prepare("
                INSERT INTO enderecos_entrega (pedido_id, cep, logradouro, numero, bairro, cidade, estado)
                VALUES (:pedido_id, :cep, :logradouro, :numero, :bairro, :cidade, :estado)
            ");
            $stmtEnd->execute([
                ':pedido_id' => $pedido_id,
                ':cep' => $cep,
                ':logradouro' => $logradouro,
                ':numero' => $numero,
                ':bairro' => $bairro,
                ':cidade' => $cidade,
                ':estado' => $estado
            ]);

            $stmtPag = $pdo->prepare("
                INSERT INTO pagamentos_pedidos (pedido_id, valor_pago, forma_pagamento, status)
                VALUES (:pedido_id, :valor_pago, :forma_pagamento, 'pendente')
            ");
            $stmtPag->execute([
                ':pedido_id' => $pedido_id,
                ':valor_pago' => $valor_total,
                ':forma_pagamento' => $forma_pagamento
            ]);

            $pdo->commit();
            header('Location: Tela_pedidos.php');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensagem_erro = "Erro ao finalizar pedido: " . $e->getMessage();
        }
    } else {
        $mensagem_erro = "A data final deve ser igual ou posterior à data inicial.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fechamento de Pedido - HeavyRent</title>
    <link rel="stylesheet" href="../../css/Usuário_css/Tela_fechamento.css">
</head>
<body>

    <main class="checkout-container">
        <h2>FECHAMENTO DO PEDIDO</h2>

        <?php if ($mensagem_erro): ?>
            <div class="alert-error"><?= htmlspecialchars($mensagem_erro) ?></div>
        <?php endif; ?>

        <form action="Tela_fechamento.php?maquinario_id=<?= $maquinario_id ?>" method="POST" class="checkout-grid">
            <input type="hidden" name="acao" value="finalizar_pedido">

            <div class="checkout-card">
                <h3>1. Maquinário Selecionado</h3>
                <div class="item-summary">
                    <img src="<?= !empty($maquina['imagem']) ? htmlspecialchars($maquina['imagem']) : '../../assets/placeholder-maquina.jpg' ?>" alt="Máquina">
                    <div>
                        <h4><?= htmlspecialchars($maquina['nome']) ?></h4>
                        <p>Valor Diária: <strong>R$ <?= number_format($maquina['valor_diaria'], 2, ',', '.') ?></strong></p>
                    </div>
                </div>

                <div class="date-selection">
                    <div class="form-group">
                        <label>Data de Início:</label>
                        <input type="date" name="data_inicio" id="dataInicio" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Data de Término:</label>
                        <input type="date" name="data_fim" id="dataFim" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                </div>
            </div>

            <div class="checkout-card">
                <h3>2. Endereço de Entrega</h3>
                <div class="form-group">
                    <label>CEP:</label>
                    <input type="text" name="cep" placeholder="00000-000" required>
                </div>
                <div class="form-group">
                    <label>Logradouro / Rua:</label>
                    <input type="text" name="logradouro" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Número:</label>
                        <input type="text" name="numero" required>
                    </div>
                    <div class="form-group">
                        <label>Bairro:</label>
                        <input type="text" name="bairro" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Cidade:</label>
                        <input type="text" name="cidade" required>
                    </div>
                    <div class="form-group">
                        <label>Estado (UF):</label>
                        <input type="text" name="estado" maxlength="2" value="SP" required>
                    </div>
                </div>
            </div>

            <div class="checkout-card full-width">
                <h3>3. Pagamento e Confirmação</h3>
                <div class="form-group">
                    <label>Forma de Pagamento:</label>
                    <select name="forma_pagamento">
                        <option value="pix">PIX</option>
                        <option value="boleto">Boleto Bancário</option>
                        <option value="cartao_credito">Cartão de Crédito</option>
                    </select>
                </div>

                <div class="total-box">
                    <span>Total Estimado:</span>
                    <span id="valorTotalDisplay">R$ <?= number_format($maquina['valor_diaria'], 2, ',', '.') ?></span>
                </div>

                <button type="submit" class="btn-confirmar">CONFIRMAR E FECHAR PEDIDO</button>
            </div>
        </form>
    </main>

    <script>
        const valorDiaria = <?= $maquina['valor_diaria'] ?>;
        const inputInicio = document.getElementById('dataInicio');
        const inputFim = document.getElementById('dataFim');
        const displayTotal = document.getElementById('valorTotalDisplay');

        function calcularTotal() {
            if (inputInicio.value && inputFim.value) {
                const d1 = new Date(inputInicio.value);
                const d2 = new Date(inputFim.value);
                const diffTime = d2 - d1;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                if (diffDays > 0) {
                    const total = diffDays * valorDiaria;
                    displayTotal.innerText = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                } else {
                    displayTotal.innerText = 'R$ 0,00';
                }
            }
        }

        inputInicio.addEventListener('change', calcularTotal);
        inputFim.addEventListener('change', calcularTotal);
    </script>
</body>
</html>
