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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: admin_menu.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM menu WHERE id_menu = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: admin_menu.php");
    exit;
}

$menu = $result->fetch_assoc();
$stmt->close();

$redir = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome  = trim($_POST['nome']);
    $link  = trim($_POST['link']);
    $tipo  = $_POST['tipo'];
    $ordem = intval($_POST['ordem']);
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if ($nome === '' || $link === '') {
        $erro = 'Preenche todos os campos obrigatórios.';
    } else {

        $stmt = $conn->prepare(
            "UPDATE menu
             SET Nome = ?, link = ?, tipo = ?, ordem = ?, ativo = ?
             WHERE id_menu = ?"
        );

        if ($stmt) {
            $stmt->bind_param(
                "sssiii",
                $nome,
                $link,
                $tipo,
                $ordem,
                $ativo,
                $id
            );

            if ($stmt->execute()) {
                $sucesso = 'Item do menu atualizado com sucesso!';
                $redir = true;
                $menu = array_merge($menu, $_POST);
                $menu['ativo'] = $ativo;

            } else {
                $erro = 'Erro ao atualizar item do menu.';
            }

            $stmt->close();
        } else {
            $erro = 'Erro na preparação da query.';
        }
    }
    
}



?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title>Editar Item do Menu</title>
    <link rel="stylesheet" href="../css/admin_criar.css">
</head>

<body>
        <a href="javascript:history.back()" class="botao-voltar voltar-fixo">
    ← Voltar
</a>

<div class="bg">
    <div class="overlay"></div>

    <div class="content">
        <form method="POST">
            <h1>Editar Item do Menu</h1>
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
            <input type="text" name="nome"
                   value="<?= htmlspecialchars($menu['Nome']) ?>" required>
<br><br>  
            <label for="link">Link</label>
            <input type="text" name="link"
                   value="<?= htmlspecialchars($menu['link']) ?>" required>
<br><br>  
            <label for="tipo">Visível para</label>
            <select name="tipo" required>
                <?php foreach ($tiposMenu as $valor => $label): ?>
                    <option value="<?= htmlspecialchars($valor) ?>"
                        <?= $menu['tipo'] === $valor ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
<br><br>  
            <label for="ordem">Ordem</label>
            <input type="number" name="ordem"
                   value="<?= (int)$menu['ordem'] ?>" required>
<br><br>  
       <div class="checkbox-linha">
    <label for="ativo">Ativo</label>
    <input type="checkbox" id="ativo" name="ativo"
        <?= $menu['ativo'] ? 'checked' : '' ?>>
</div>
                <br>
            <div align="center">
                <button type="submit" class="botao">Guardar Alterações</button>
            </div>
            <br>

        </form>
    </div>
</div>
</body>
</html>
