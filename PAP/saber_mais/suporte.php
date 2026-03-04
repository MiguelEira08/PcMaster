<?php
include_once __DIR__ . '/../cabecindex.php';
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';
require_once '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensagem_sucesso = '';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motivo = trim($_POST['motivo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $data_envio = date("Y-m-d H:i:s");

    if ($nome === '' ||$email === '' || $motivo === '' || $mensagem === '') {
        $erros[] = 'Todos os campos são obrigatórios.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido.';
    }

    if (empty($erros)) {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'pcmastergeral@gmail.com';
            $mail->Password   = 'iuiv lkdy abyt xojv'; // ⚠️ POR FAVOR, ELIMINA A PASS ANTIGA DA TUA CONTA GOOGLE
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
            $mail->addAddress('migueleira08@gmail.com', 'Administrador');
            $mail->addAddress('gustavofigueiredo.a.f@gmail.com', 'Administrador');
            
            $mail->isHTML(true);
            $mail->Subject = 'Suporte ao Cliente - PcMaster';

            // Protege o texto da mensagem contra código malicioso e converte quebras de linha
            $mensagem_segura = nl2br(htmlspecialchars($mensagem));

            $mail->Body = "
            <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                    
                    <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);'>Suporte ao Cliente</h1>
                    </div>
                    
                    <div style='padding: 30px;'>
                        <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Novo pedido de suporte do cliente recebido.</p>
                        
                        <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Detalhes do Cliente</h3>
                        
                        <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0; margin-bottom: 25px;'>
                            <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Nome:</strong> {$nome}</p>
                            <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Email:</strong> <a href='mailto:{$email}' style='color: #0056b3; text-decoration: none;'>{$email}</a></p>
                            <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Data:</strong> {$data_envio}</p>
                            <p style='margin: 0; font-size: 14px; color: #333333;'><strong>Motivo:</strong> <span style='background-color: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 13px;'>{$motivo}</span></p>
                        </div>

                        <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Mensagem</h3>

                        <div style='background-color: #eef5ff; border: 1px solid #cce0ff; padding: 20px; border-radius: 6px;'>
                            <p style='margin: 0; font-size: 15px; line-height: 1.6; color: #004085;'>{$mensagem_segura}</p>
                        </div>
                    </div>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                        <p style='margin: 0; font-size: 14px; color: #888888;'><strong style='color: burlywood;'>PcMaster</strong></p>
                    </div>
                    
                </div>
            </div>
            ";

            $mail->send();
            $mensagem_sucesso = 'Pedido de suporte enviado com sucesso! Voltando em 1 segundo...';
            $redir = true;
        } catch (Exception $e) {
            $erros[] = 'Erro ao enviar o pedido de suporte.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Suporte</title>
    <link rel="stylesheet" href="../css/conta.css">
</head>
<body>
       <a href="../saber_mais/saber_mais.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>
    <div class="content">

        

        <form method="POST">

            <?php if ($mensagem_sucesso): ?>
                <p class="success-message"><?= htmlspecialchars($mensagem_sucesso) ?></p>
            <?php if (!empty($redir)): ?>
        <script>
            setTimeout(function () {
                window.location.href = '../saber_mais/saber_mais.php';
            }, 1000);
        </script>
    <?php endif; ?>
            <?php endif; ?>

            <?php if ($erros): ?>
                <ul class="error-message">
                    <?php foreach ($erros as $erro): ?>
                        <li><?= htmlspecialchars($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div style="text-align: center;">
                <h1 style="color: white;">Contactar Suporte</h1>
                <br>
                <label>Nome de Utilizador:</label><br>
                <input type="nome" name="nome" maxlength="250" required>

                <label>Email:</label><br>
                <input type="email" name="email" maxlength="250" required>

                <label>Motivo do contacto:</label><br>
                <input type="text" name="motivo" maxlength="250" required>

                <label>Explicação:</label><br>
                <input type="text" name="mensagem" maxlength="500" required>
                <div align="center">
                    <button type="submit" class="botao">Enviar Pedido</button>
                </div>
                <br>

            </div>
        </form>

    </div>
</div>

</body>
</html>
