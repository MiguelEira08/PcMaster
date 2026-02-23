<?php
session_start();
require_once '../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';
require_once '../PHPMailer/Exception.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $nome = trim($_POST['username']);
    $numtel = trim($_POST['telefone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $caminho_arquivo = './imagens/user.png';

    if (empty($email) || empty($nome) || empty($password) || empty($confirm_password) || empty($numtel)) {
        $erro = 'Preencha todos os campos!';
    } elseif (!preg_match('/^\d{9}$/', $numtel)) {
        $erro = 'O número de telemóvel deve conter exatamente 9 dígitos.';
    } elseif ($password !== $confirm_password) {
        $erro = 'As passwords não coincidem!';
    } else {

        $stmt = $conn->prepare("
            SELECT id FROM utilizadores WHERE email = ?
            UNION
            SELECT id FROM verificacao_utilizadores WHERE email = ?
        ");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $erro = 'Email já está registado!';
        } else {
            $stmt->close();

            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {

                $pasta = '../imagens/';
                if (!is_dir($pasta)) {
                    mkdir($pasta, 0777, true);
                }

                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($ext, $permitidas)) {
                    $nome_ficheiro = uniqid('perfil_') . '.' . $ext;
                    $destino = $pasta . $nome_ficheiro;

                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                        $caminho_arquivo = './imagens/' . $nome_ficheiro;
                    }
                } else {
                    $erro = 'Formato de imagem inválido!';
                }
            }
            $codigo = random_int(100000, 999999);

            if (!$erro) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

               $stmt = $conn->prepare("
    INSERT INTO verificacao_utilizadores
    (nome, email, numtel, password, caminho_arquivo, tipo, Verificada, duracao, codigo_verificacao)
    VALUES (?, ?, ?, ?, ?, 'utilizador', 'nao', NOW(), ?)
");

$stmt->bind_param("ssssss", $nome, $email, $numtel, $hashed_password, $caminho_arquivo, $codigo);


                if ($stmt->execute()) {

                    $mail = new PHPMailer(true);
                    try {
                        $mail->CharSet = 'UTF-8';
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'pcmastergeral@gmail.com';
                        $mail->Password = 'mjsv oxar shbz dfzp';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                        $mail->addAddress($email, $nome);

                        $mail->isHTML(true);
                        $mail->Subject = 'Verifique a sua conta - PcMaster';

                        $link = "http://localhost/PcMaster/PAP/login/verificar_conta.php?email=" . urlencode($email);

                      $mail->Body = "
    <h2>Olá, $nome!</h2>
    <p>Obrigado por se registar na PcMaster.</p>
    <p>O seu código de verificação é:</p>

    <h1 style='letter-spacing:5px;'>$codigo</h1>

    <p>Introduza este código no site para verificar a sua conta.</p>
    <p><small>Este código é válido por tempo limitado.</small></p>
";

                        $mail->send();
                    } catch (Exception $e) {}

                   header("Location: verificar_codigo.php?email=" . urlencode($email));
exit();

                } else {
                    $erro = 'Erro ao registar!';
                }

                $stmt->close();
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
    <title>Registar</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="bg">
    <div class="overlay">
        <center>
        <form method="POST" enctype="multipart/form-data">
            <h1 class="amiko-semibold"  style="color: black;">Registar</h1>
<br>
            <?php if ($erro): ?>
                <p style="color:red"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <label class="amiko-semibold">Nome de utilizador:</label>
            <input type="text" name="username" style="font-family: 'Poppins', sans-serif;" required><br>

            <label class="amiko-semibold">Email:</label>
            <input type="email" name="email" style="font-family: 'Poppins', sans-serif;" required><br>

            <label class="amiko-semibold">Telefone:</label>
            <input type="text" name="telefone"  style="font-family: 'Poppins', sans-serif;"pattern="\d{9}" maxlength="9" required><br>

            <label class="amiko-semibold">Foto de perfil:</label>
            <input type="file" name="foto" accept="image/*" class="amiko-semibold"><br>

            <label class="amiko-semibold">Password:</label>
            <input type="password" name="password" required minlength="6"><br>

            <label class="amiko-semibold">Confirmar Password:</label>
            <input type="password" name="confirm_password" required minlength="6"><br>
            <br>
            <button type="submit">Registar</button>
            <p>Já tem conta? <a href="login.php">Iniciar Sessão</a></p>
        </form>
        </center>
    </div>
</div>
</body>
</html>
