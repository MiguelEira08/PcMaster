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
    $mail->Subject = 'Recuperação da Palavra-Passe';
    $mail->Body = 'Olá, <br> Para repor a sua palavra-passe, clique no link abaixo.
    <br><br><a href="http://localhost/PcMaster/PAP/login/novapasse.php?email=' . urlencode($email) . '">Clique Aqui</a>
    <br><br>Atenciosamente, <br> Equipa PcMaster';

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
