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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $erro = 'ID inválido.';
} else {
    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM utilizadores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $erro = 'Utilizador não encontrado.';
    } else {
        $utilizador = $result->fetch_assoc();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nome      = trim($_POST['nome']);
            $email     = trim($_POST['email']);
            $numtel    = trim($_POST['numtel']);
            $senhaNova = trim($_POST['password']);
            $tipo      = $_POST['tipo'];

            if (empty($nome) || empty($email) || empty($numtel) || empty($tipo)) {
                $erro = 'Nome, e-mail, telefone e tipo são obrigatórios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = 'E-mail inválido.';
            } elseif (!preg_match('/^\d{9}$/', $numtel)) {
                $erro = 'Telefone inválido.';
            } else {

                $caminho_arquivo = $utilizador['caminho_arquivo'];

                if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {

                    $pasta = "../imagens/";
                    if (!is_dir($pasta)) {
                        mkdir($pasta, 0777, true);
                    }

                    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($ext, $permitidas)) {
                        $nome_ficheiro = uniqid('perfil_') . '.' . $ext;
                        $destino = $pasta . $nome_ficheiro;

                        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                            $caminho_arquivo = "imagens/" . $nome_ficheiro;
                        }
                    } else {
                        $erro = "Formato de imagem inválido!";
                    }
                }

                if (!$erro) {
                    if ($senhaNova !== '') {
                        $hash = password_hash($senhaNova, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("
                            UPDATE utilizadores
                               SET nome = ?, email = ?, numtel = ?, password = ?, tipo = ?, caminho_arquivo = ?
                             WHERE id = ?
                        ");
                        $stmt->bind_param("ssssssi", $nome, $email, $numtel, $hash, $tipo, $caminho_arquivo, $id);
                    } else {
                        $stmt = $conn->prepare("
                            UPDATE utilizadores
                               SET nome = ?, email = ?, numtel = ?, tipo = ?, caminho_arquivo = ?
                             WHERE id = ?
                        ");
                        $stmt->bind_param("sssssi", $nome, $email, $numtel, $tipo, $caminho_arquivo, $id);
                    }

                    if ($stmt->execute()) {
                        $sucesso = 'Utilizador atualizado com sucesso!';

                        $utilizador['nome'] = $nome;
                        $utilizador['email'] = $email;
                        $utilizador['numtel'] = $numtel;
                        $utilizador['tipo'] = $tipo;
                        $utilizador['caminho_arquivo'] = $caminho_arquivo;

                    } else {
                        $erro = 'Erro ao atualizar utilizador.';
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title>Editar Utilizador</title>
    <link rel="stylesheet" href="../css/admin_criar.css">
</head>
<body>   
<a href="javascript:history.back()" class="botao-voltar voltar-fixo">
    ← Voltar
</a>


<div class="bg">
 <div class="overlay"></div>
 <div class="content">

  <form method="POST" enctype="multipart/form-data">
    <h1>Editar Utilizador</h1>

    <?php if ($erro): ?>
        <p class="error-message"><?= htmlspecialchars($erro) ?></p>
    <?php elseif ($sucesso): ?>
        <p class="success-message"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <?php if (isset($utilizador)): ?>

        <label>Foto de Perfil Atual:</label>
        <br><br>
        <img src="../<?= $utilizador['caminho_arquivo'] ?>" 
             style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:2px solid #fff; font-family: 'Poppins', sans-serif;">
        <br><br>

        <label>Alterar Foto:</label>
        <input type="file" name="foto" accept="image/*">
 <br><br>
        <label>Nome:</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($utilizador['nome']) ?>" required>
 <br><br>
        <label>E-mail:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($utilizador['email']) ?>" required>
 <br><br>
        <label>Telefone (9 dígitos):</label>
        <input type="text" name="numtel" value="<?= htmlspecialchars($utilizador['numtel']) ?>"
               required pattern="\d{9}" title="Introduza 9 dígitos">
 <br><br>
        <label>Nova password:<br><small>(Deixe em branco para manter a atual)</small></label>
        <input type="password" name="password" minlength="6">
 <br><br>
        <label>Tipo:</label>
        <select name="tipo" required>
            <option value="utilizador" <?= $utilizador['tipo'] === 'utilizador' ? 'selected' : '' ?>>Utilizador</option>
            <option value="admin" <?= $utilizador['tipo'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>

        <br><br>
        <div align="center"><button type="submit" class="botao">Guardar alterações</button></div>
    
    <?php endif; ?>

  </form>

 </div>
</div>

</body>
</html>
