<?php
session_start();
require_once __DIR__ . '/../db.php';
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = '';
$erro = '';
$erro_email = '';
$email_pref = '';

if (isset($_GET['email'])) {
    $email_pref = trim($_GET['email']);

    $stmt = $conn->prepare("SELECT id FROM utilizadores WHERE email = ?");
    $stmt->bind_param("s", $email_pref);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $erro_email = 'Este email não está associado a nenhuma conta!';
        $email_pref = ''; 
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $nova_pass = trim($_POST['nova_pass']);
    $confirmar_pass = trim($_POST['confirmar_pass']);

    if (empty($email) || empty($nova_pass) || empty($confirmar_pass)) {
        $erro = 'Preencha todos os campos!';
    } elseif ($nova_pass !== $confirmar_pass) {
        $erro = 'As palavras-passe não coincidem!';
    } else {
        $stmt = $conn->prepare("SELECT id FROM utilizadores WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $nova_hash = password_hash($nova_pass, PASSWORD_DEFAULT);

            $stmt_update = $conn->prepare("UPDATE utilizadores SET password = ? WHERE email = ?");
            $stmt_update->bind_param("ss", $nova_hash, $email);

            if ($stmt_update->execute()) {
                try {
                    $mail = new PHPMailer(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'pcmastergeral@gmail.com'; 
                    $mail->Password   = 'mjsv oxar shbz dfzp'; 
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Nova palavra-passe - PcMaster';
                    $mail->Body = "
                    <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
                        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                            
                            <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>Equipa PcMaster</h1>
                            </div>
                            
                            <div style='padding: 30px;'>
                                <p style='font-size: 16px; margin-top: 0;'>Olá,</p>
                                <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Este é um aviso automático sobre a segurança da sua conta.</p>
                                
                                <div style='background-color: #eef5ff; border: 1px solid #cce0ff; padding: 20px; border-radius: 6px; margin: 25px 0;'>
                                    <p style='margin: 0; font-size: 15px; line-height: 1.6; color: #004085; text-align: center;'>
                                        <strong>A sua palavra-passe foi alterada com sucesso.</strong>
                                    </p>
                                </div>

                                <div style='background-color: #f8f9fa; border-left: 4px solid #adb5bd; padding: 15px; margin: 25px 0; border-radius: 0 4px 4px 0;'>
                                    <p style='margin: 0; font-size: 14px; color: #666666;'>
                                        <strong>Não foi você?</strong><br>
                                        Se não solicitou esta alteração, por favor contacte o nosso suporte imediatamente ou tente recuperar o acesso através da página de login.
                                    </p>
                                </div>

                                <p style='font-size: 15px; line-height: 1.6; margin-top: 30px; color: #555555;'>Para sua segurança, nunca partilhe os seus dados de acesso com terceiros.</p>
                            </div>
                            
                            <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                    <p style='margin: 0; font-size: 14px; color: #888888;'>Obrigado por escolher a <strong style='color: burlywood;'>PcMaster</strong>!</p>
                            </div>
                        </div>
                    </div>
                    ";

                    $mail->send();
                } catch (Exception $e) {
                }

                $mensagem = 'Palavra-passe alterada com sucesso! A redirecionar para o login...';
            } else {
                $erro = 'Erro ao atualizar a palavra-passe. Tente novamente.';
            }

            $stmt_update->close();
        } else {
            $erro = 'Este email não está associado a nenhuma conta!';
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Palavra-Passe</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiko:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="bg">
    <div class="overlay">
        <center>
            <form method="POST" action="">
                <h2 class="amiko-semibold">Repor Palavra-Passe</h2>
                <img src="../imagens/logo.png" height="200px" width="200px" alt="Logo">

                <?php if ($erro_email): ?>
                    <p style="color: #ff4d4d; font-weight: bold;"> <?= htmlspecialchars($erro_email) ?> </p>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <p style="color: #ff4d4d; font-weight: bold;"> <?= htmlspecialchars($erro) ?> </p>
                <?php endif; ?>

                <?php if ($mensagem): ?>
                    <p style="color: #000000; font-weight: bold;"> <?= htmlspecialchars($mensagem) ?> </p>
                    <script>
                        setTimeout(function() {
                            window.location.href = "../login/login.php";
                        }, 3000);
                    </script>
                <?php endif; ?>

                <?php if (!$mensagem && !$erro_email): ?>
                    <label class="amiko-semibold">Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($email_pref) ?>" readonly><br>

                    <label class="amiko-semibold">Nova Palavra-Passe</label>
                    <input type="password" name="nova_pass" required><br>

                    <label class="amiko-semibold">Confirmar Palavra-Passe</label>
                    <input type="password" name="confirmar_pass" required><br>

                    <button type="submit">Alterar Palavra-Passe</button>
                    <br><br>
                    <p class="amiko-semibold"><a href="login.php">Voltar ao Login</a></p>
                <?php endif; ?>
            </form>
        </center>
    </div>
</div>
</body>
</html>
