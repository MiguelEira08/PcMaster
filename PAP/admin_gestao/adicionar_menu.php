<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}

$erro = '';
$sucesso = '';

$tiposMenu = [
    'todos' => 'Todos',
    'guest' => 'Visitante',
    'utilizador' => 'Utilizador',
    'admin' => 'Admin'
];
$redir = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome  = trim($_POST['nome']);
    $link  = trim($_POST['link']);
    $tipo  = $_POST['tipo'];
    $ordem = intval($_POST['ordem']);
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if ($nome === '' || $link === '') {
        $erro = 'Todos os campos obrigatórios devem ser preenchidos.';
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO menu (Nome, link, tipo, ordem, ativo)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param(
                "sssii",
                $nome,
                $link,
                $tipo,
                $ordem,
                $ativo
            );

            if ($stmt->execute()) {
                $sucesso = 'Item do menu adicionado com sucesso!';
                $redir=true;
            } else {
                $erro = 'Erro ao adicionar item ao menu.';
            }

            $stmt->close();
        } else {
            $erro = 'Erro na preparação da query: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title>Adicionar Item ao Menu</title>
    <link rel="stylesheet" href="../css/admin_criar.css">
</head>

<body>
<div class="botao-link botao-voltar voltar-fixo" onclick="window.location.href='../admin/admin_menu.php'">← Voltar</div>

<div class="bg">
    <div class="overlay"></div>

    <div class="content">
        <form method="POST">
            <h1>Adicionar Item ao Menu</h1>
<br>
   <?php if ($erro): ?>
    <p class="error-message"><?= htmlspecialchars($erro) ?></p>

<?php elseif ($sucesso): ?>
    <p style="color: black; font-weight: bold;">
        
        <?= htmlspecialchars($sucesso) ?><br>
        <small>A voltar ao menu em 1 segundo . . . </small>
    </p>
    <br>
    <?php if (!empty($redir)): ?>
        <script>
            setTimeout(function () {
                window.location.href = '../admin/admin_menu.php';
            }, 1000);
        </script>
    <?php endif; ?>
<?php endif; ?>

            <label for="nome">Nome do Menu</label>
            <input type="text" name="nome" required>
<br><br>
            <label for="link">Link</label>
            <input type="text" name="link" placeholder="ex: index.php" required>
<br><br>
            <label for="tipo">Visível para</label>
            <select name="tipo" required>
                <?php foreach ($tiposMenu as $valor => $label): ?>
                    <option value="<?= htmlspecialchars($valor) ?>">
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
<br><br>
            <label for="ordem">Ordem</label>
            <input type="number" name="ordem" value="1" required>
<br><br>
            <div class="checkbox-linha">
                <label for="ativo">Ativo</label>
                <input type="checkbox" id="ativo" name="ativo" checked>
            </div>
            </label>

<br>
            <div align="center">
                <button type="submit" class="botao">Adicionar ao Menu</button>
            </div>
            <br>

        </form>
    </div>
</div>
</body>
</html>
