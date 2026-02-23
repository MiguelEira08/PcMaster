<?php
session_start();
require_once __DIR__ . '/../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$erro = '';
$nome_digitado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $password = $_POST['password'];
    $nome_digitado = htmlspecialchars($nome);

    if ($nome === '' || $password === '') {
        $erro = 'Preencha todos os campos!';
    } else {

        $stmt = $conn->prepare("
            SELECT id, nome, password, tipo 
            FROM utilizadores 
            WHERE nome = ?
        ");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $erro = 'Utilizador não encontrado!';
            $stmt->close();
        } else {

            $stmt->bind_result($id, $nome_bd, $hash, $tipo);
            $stmt->fetch();
            $stmt->close();

            $conn->query("
                INSERT IGNORE INTO utilizador_seguranca (utilizador_id)
                VALUES ($id)
            ");

            $sec = $conn->prepare("
                SELECT tentativas, bloqueado
                FROM utilizador_seguranca
                WHERE utilizador_id = ?
            ");
            $sec->bind_param("i", $id);
            $sec->execute();
            $sec->bind_result($tentativas, $bloqueado);
            $sec->fetch();
            $sec->close();

            $tentativas = (int)$tentativas;

            if ($bloqueado === 'sim') {
                $erro = 'Conta bloqueada. Contacte um administrador.';
            } else {
                if (password_verify($password, $hash)) {

                    $upd = $conn->prepare("
                        UPDATE utilizador_seguranca
                        SET tentativas = 0,
                            ultimo_login = NOW()
                        WHERE utilizador_id = ?
                    ");
                    $upd->bind_param("i", $id);
                    $upd->execute();
                    $upd->close();

                    $_SESSION['user_id'] = $id;
                    $_SESSION['nome'] = $nome_bd;
                    $_SESSION['tipo'] = $tipo;

                    if (isset($_SESSION['redirect_after_login'])) {
                        $url = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        header("Location: $url");
                        exit();
                    }

                    if ($tipo === 'admin') {
                        header('Location: ../admin/admin_dashboard.php');
                    } else {
                        header('Location: ../index/index.php');
                    }
                    exit();

                } else {

                    $tentativas++;
                    $bloqueado_update = $tentativas >= 5 ? 'sim' : 'nao';

                    $upd = $conn->prepare("
                        UPDATE utilizador_seguranca
                        SET tentativas = ?, bloqueado = ?
                        WHERE utilizador_id = ?
                    ");
                    $upd->bind_param("isi", $tentativas, $bloqueado_update, $id);
                    $upd->execute();
                    $upd->close();

                    if ($bloqueado_update === 'sim') {
                        $erro = 'Conta bloqueada por excesso de tentativas.';
                    } else {
                        $erro = 'Senha incorreta! Tentativas restantes: ' . (5 - $tentativas);
                    }
                }
            }
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiko:wght@600&display=swap" rel="stylesheet">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
        <a href="../index/index.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay">
        <center>
        <form method="POST" action="">
            <b><h2 class="amiko-semibold">Login</h2></b>
            <img src="../imagens/logo.png" height="200px" width="200px">

            <?php if ($erro): ?>
                <p style="color: red;"> <?= htmlspecialchars($erro) ?> </p>
            <?php endif; ?>

            <label class="amiko-semibold">Nome de Utilizador</label>
            <input type="text" name="nome"  style="font-family: 'Poppins', sans-serif;" value="<?= $nome_digitado ?>" required><br>

            <label class="amiko-semibold">Password:</label>
            <input type="password" name="password" class="amiko-semibold" required><br>

            <button type="submit">Entrar</button>
            <br>
            <p class="amiko-semibold"><a href="recuperarpasse.php">Esqueceu-se da palavra-passe?</a><br><br></p>
            <p class="amiko-semibold">Ainda não tem conta? <a href="registar.php">Registar</a></p>
            
        </form>
    </div>
</div>
</body>
</html>
