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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $numtel   = trim($_POST['numtel']);
    $password = $_POST['password'];
    $tipo     = $_POST['tipo'];

    if (empty($nome) || empty($email) || empty($numtel) || empty($password) || empty($tipo)) {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (!preg_match('/^\d{9}$/', $numtel)) { 
        $erro = 'Número de telefone inválido.';
    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $caminho_arquivo = "imagens/user.png";

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
            $stmt = $conn->prepare(
                "INSERT INTO utilizadores (nome, email, numtel, password, tipo, caminho_arquivo) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            if ($stmt) {
                $stmt->bind_param("ssssss", $nome, $email, $numtel, $hash, $tipo, $caminho_arquivo);

                if ($stmt->execute()) {
                    $sucesso = 'Utilizador adicionado com sucesso!';
                } else {
                    $erro = 'Erro ao adicionar utilizador: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $erro = 'Erro na preparação da query: ' . $conn->error;
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
    <title>Adicionar Utilizador</title>
    <link rel="stylesheet" href="../css/admin_criar.css">
</head>
<body>
<div class="botao-link botao-voltar voltar-fixo" onclick="window.location.href='../admin/gerir_utilizadores.php'">← Voltar</div>

<div class="bg">
  <div class="overlay"></div>
    <div class="content">

      <form method="POST" enctype="multipart/form-data">
        <h1>Adicionar Utilizador</h1>

        <?php if ($erro): ?>
          <p class="error-message"><?= htmlspecialchars($erro) ?></p>
        <?php elseif ($sucesso): ?>
          <p style="color: green; font-weight: bold;"><?= htmlspecialchars($sucesso) ?></p>
        <?php endif; ?>
<br><br>  
        <label for="nome">Nome</label>
        <input type="text" name="nome" value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>" required>
<br><br>  

        <label for="email">E-mail</label>
        <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
<br><br>  
        <label for="numtel"> Nº de Telefone (9 dígitos)</label>
        <input type="text" name="numtel" value="<?= isset($_POST['numtel']) ? htmlspecialchars($_POST['numtel']) : '' ?>" required pattern="\d{9}" title="Introduza 9 dígitos.">
<br><br>  
        <label for="password">Password</label>
        <input type="password" name="password" required minlength="6">
<br><br>  
        <label for="tipo">Tipo</label>
        <select name="tipo" required>
            <option value="utilizador" <?= isset($_POST['tipo']) && $_POST['tipo'] === 'utilizador' ? 'selected' : '' ?>>Utilizador</option>
            <option value="admin" <?= isset($_POST['tipo']) && $_POST['tipo'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
<br><br>  
        <label for="foto">Foto de Perfil</label>
        <input type="file" name="foto" accept="image/*">

        <br><br>
        <div align="center"><button type="submit" class="botao">Adicionar Utilizador</button></div>


      </form>

    </div>
  </div>
</div>
</body>
</html>
