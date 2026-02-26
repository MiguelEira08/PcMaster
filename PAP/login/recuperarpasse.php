<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['nome'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email inválido.";
    } else {
               try {
    require '../phpmailer/PHPMailer.php';
    require '../phpmailer/SMTP.php';
    require '../phpmailer/Exception.php';

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
    $mail->Subject = 'Recuperação de Palavra-Passe - PcMaster';

    // Definir o link para facilitar a leitura do código
    $link_recuperacao = "http://localhost/PcMaster/PAP/login/novapasse.php?email=" . urlencode($email);

    $mail->Body = "
    <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            
            <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>Equipa PcMaster</h1>
            </div>
            
            <div style='padding: 30px;'>
                <p style='font-size: 16px; margin-top: 0;'>Olá,</p>
                <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Recebemos um pedido para redefinir a palavra-passe da sua conta. Se não fez este pedido, pode ignorar este e-mail.</p>
                
                <div style='text-align: center; margin: 35px 0;'>
                    <a href='{$link_recuperacao}' 
                    style='background-color: #0056b3; color: #ffffff; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                    Redefinir Palavra-Passe
                    </a>
                </div>

                <div style='background-color: #f8f9fa; border-left: 4px solid #adb5bd; padding: 15px; margin-top: 25px; border-radius: 0 4px 4px 0;'>
                    <p style='margin: 0; font-size: 13px; color: #666666; line-height: 1.4;'>
                        <strong>Dificuldades com o botão?</strong><br> 
                        Copie e cole o seguinte link no seu navegador:<br>
                        <span style='color: #0056b3; word-break: break-all;'>{$link_recuperacao}</span>
                    </p>
                </div>
                
                <p style='font-size: 15px; line-height: 1.6; margin-top: 30px; color: #555555;'>Este link expirará em breve por motivos de segurança.</p>
            </div>
            
            <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                    <p style='margin: 0; font-size: 14px; color: #888888;'>Obrigado por escolher a <strong style='color: burlywood;'>PcMaster</strong>!</p>
            </div>
        </div>
    </div>
    ";
    if ($mail->send()) {
        echo 'Um link de recuperação foi enviado para o seu e-mail.';
        header('Location: login.php');
    }
} catch (Exception $e) {
    echo "Erro ao enviar o e-mail. Erro: {$mail->ErrorInfo}";
}

    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title>Recuperar Palavra-Passe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiko:wght@600&display=swap" rel="stylesheet">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <a href="../login/login.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay">
        <center>
        <form method="POST" action="">
            <h1 class="amiko-semibold" style="color: black;">Repor Palavra-Passe</h1>
            <img src="../imagens/logo.png" height="200px" width="200px">

            <label class="amiko-semibold">Email de Recuperação</label>
            <input type="text" name="nome" style="font-family: 'Poppins', sans-serif;" required><br>

            <button type="submit">Enviar</button>
    
        </form>
    </div>
</div>
</body>
</html>
