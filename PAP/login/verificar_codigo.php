<?php
session_start();
require_once '../db.php';

$erro = '';
$mensagem = '';

$email = isset($_GET['email']) ? trim($_GET['email']) : '';

/* =========================
   QUANDO O FORM É ENVIADO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $codigo = trim($_POST['codigo']);

    // Procurar utilizador com email + código
    $stmt = $conn->prepare("
       SELECT * FROM verificacao_utilizadores
WHERE email = ?
  AND codigo_verificacao = ?
  AND duracao >= (NOW() - INTERVAL 10 MINUTE)

    ");
    $stmt->bind_param("ss", $email, $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

if (!$user) {
    $erro = "Código inválido ou expirado. Peça um novo código.";
    } else {

        /* =========================
           MOVER PARA TABELA FINAL
        ========================= */

        $stmt = $conn->prepare("
            INSERT INTO utilizadores 
            (nome, email, numtel, password, caminho_arquivo, tipo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssss",
            $user['nome'],
            $user['email'],
            $user['numtel'],
            $user['password'],
            $user['caminho_arquivo'],
            $user['tipo']
        );

        $stmt->execute();
        $stmt->close();

        /* =========================
           APAGAR DA VERIFICAÇÃO
        ========================= */

        $del = $conn->prepare("DELETE FROM verificacao_utilizadores WHERE email = ?");
        $del->bind_param("s", $email);
        $del->execute();
        $del->close();

        $mensagem = "Conta verificada com sucesso! A redirecionar para o login...";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Verificar Conta</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="bg">
    <div class="overlay">
        <center>

            <form method="POST">

                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                <h2 class="amiko-semibold" style="color:black;">Verificação de Conta</h2>
                <img src="../imagens/logo.png" height="180px">

                <?php if ($erro): ?>
                    <p style="color:black;"><?= $erro ?></p>
                <?php endif; ?>

                <?php if ($mensagem): ?>
                    <p style="color:black   ;"><?= $mensagem ?></p>
                    <script>
                        setTimeout(() => {
                            window.location.href = "login.php";
                        }, 2500);
                    </script>
                <?php endif; ?>

                <?php if (!$mensagem): ?>
                    <label class="amiko-semibold">Introduz o código de 6 dígitos:</label>
                    <input 
                        type="text" 
                        name="codigo" 
                        maxlength="6" 
                        pattern="\d{6}" 
                        placeholder="Ex: 123456"
                        required
                        style="font-family: 'Poppins', sans-serif;"     
                    >

                    <br><br>
                    <button type="submit">Verificar Conta</button>
                    <p style="margin-top:15px;">
                    Não recebeu o código?
                    <a href="reenviar_codigo.php?email=<?= urlencode($email) ?>">
                    Reenviar código
                    </a>
</p>

                <?php endif; ?>

            </form>

        </center>
    </div>
</div>
</body>
</html>
