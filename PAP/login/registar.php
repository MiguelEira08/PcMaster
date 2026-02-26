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
                        $mail->Subject = 'Verificar conta - PcMaster';

                        $link = "http://localhost/PcMaster/PAP/login/verificar_conta.php?email=" . urlencode($email);

                        $mail->Body = "
                        <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
                            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                                
                                <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                                    <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>Bem-vindo à PcMaster!</h1>
                                </div>
                                
                                <div style='padding: 30px; text-align: center;'>
                                    <p style='font-size: 18px; margin-top: 0; color: #333333;'>Olá, <strong>{$nome}</strong>!</p>
                                    <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Obrigado por se registar na nossa plataforma. Estamos muito contentes por o ter connosco!</p>
                                    
                                    <p style='font-size: 15px; color: #555555; margin-top: 20px;'>Para ativar a sua conta, utilize o código abaixo:</p>

                                    <div style='background-color: #f8f9fa; border: 2px dashed burlywood; padding: 20px; margin: 25px 0; border-radius: 10px;'>
                                        <h1 style='margin: 0; font-size: 38px; letter-spacing: 8px; color: #0056b3; font-family: monospace;'>{$codigo}</h1>
                                    </div>

                                    <p style='font-size: 15px; line-height: 1.6; color: #555555;'>
                                        Introduza este código na página de verificação para concluir o seu registo.
                                    </p>

                                    <p style='margin-top: 25px; font-size: 13px; color: #999999;'>
                                        <small>Este código é válido por tempo limitado para garantir a segurança da sua conta.</small>
                                    </p>
                                </div>
                                
                                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                    <p style='margin: 0; font-size: 14px; color: #888888;'>Bem-vindo à nossa comunidade!<br><strong style='color: #333333;'>Equipa PcMaster</strong></p>
                                </div>
                            </div>
                        </div>
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
