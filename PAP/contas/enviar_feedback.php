<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';
require_once '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$utilizador_id = (int) $_SESSION['user_id'];
$mensagem_sucesso = '';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motivo = trim($_POST['Motivo'] ?? '');
    $mensagem = trim($_POST['feedback'] ?? '');
    $origem = trim($_POST['origem_pagina'] ?? '');
    $status = 'por ler';
    $data_envio = date("Y-m-d H:i:s");

    if ($motivo === '' || $mensagem === '') {
        $erros[] = 'Motivo e mensagem são obrigatórios.';
    }

    if (empty($erros)) {
        $stmtNome = $conn->prepare("SELECT nome FROM utilizadores WHERE id = ?");
        $stmtNome->bind_param("i", $utilizador_id);
        $stmtNome->execute();
        $resultNome = $stmtNome->get_result();
        $nomeUtilizador = $resultNome->fetch_assoc()['nome'] ?? 'Utilizador Desconhecido';
        $stmtNome->close();

        $stmt = $conn->prepare("
            INSERT INTO feedback (user_id, Motivo, feedback, origem_pagina, data_envio, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            $erros[] = "Erro ao preparar statement: " . $conn->error;
        } else {
            $stmt->bind_param("isssss", $utilizador_id, $motivo, $mensagem, $origem, $data_envio, $status);
            $stmt->execute();
            $stmt->close();
            $mensagem_sucesso = "Feedback enviado com sucesso! Voltando em 1 segundo ...";

            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'pcmastergeral@gmail.com';
                $mail->Password   = 'iuiv lkdy abyt xojv'; // ⚠️ ATUALIZA AQUI A TUA PASSWORD
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                $mail->addAddress('migueleira08@gmail.com', 'Administrador');
                $mail->addAddress('gustavofigueiredo.a.f@gmail.com', 'Administrador'); 
                $mail->isHTML(true);
                $mail->Subject = "Feedback Recebido - PcMaster";

                // Proteção contra quebras no HTML por texto inserido pelo utilizador
                $mensagem_segura = nl2br(htmlspecialchars($mensagem));
                $origem_segura = htmlspecialchars($origem ?: 'Não especificado');
                $motivo_seguro = htmlspecialchars($motivo);

                $mail->Body = "
                <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                        
                        <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);'>Novo Feedback!</h1>
                        </div>
                        
                        <div style='padding: 30px;'>
                            <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Novo feedback recebido.</p>
                            
                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Detalhes do Utilizador</h3>
                            
                            <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0; margin-bottom: 25px;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Nome:</strong> {$nomeUtilizador}</p>
                                <p style='margin: 0; font-size: 14px; color: #333333;'><strong>Data de Envio:</strong> {$data_envio}</p>
                            </div>

                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Contexto do Feedback</h3>
                            
                            <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0; margin-bottom: 25px;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Origem do Erro/Página:</strong> <span style='background-color: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 13px;'>{$origem_segura}</span></p>
                                <p style='margin: 0; font-size: 14px; color: #333333;'><strong>Motivo:</strong> <strong style='color: #dc3545;'>{$motivo_seguro}</strong></p>
                            </div>

                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Mensagem do Cliente</h3>

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
                $redir = true;
            } catch (Exception $e) {
                error_log("Erro ao enviar email para o admin: " . $mail->ErrorInfo);
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
    <title>Enviar Feedback</title>
    <link rel="stylesheet" href="../css/conta.css">
</head>
<body>
           <a href="../contas/conta.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>
    <div class="content">
        

        <form method="POST" action="">
        <?php if (!empty($mensagem_sucesso)): ?>
            <p class="success-message"><?= htmlspecialchars($mensagem_sucesso) ?></p>
            <?php if (!empty($redir)): ?>
                <script>
                    setTimeout(function () {
                        window.location.href = 'conta.php';
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
        <h1>Enviar Feedback</h1>
        <br>
            <div style="text-align: center;">
            <label for="Motivo">Motivo do Feedback:</label>
            <input type="text" name="Motivo" id="Motivo" maxlength="250" required><br>

            <label for="origem_pagina">Origem (página ou funcionalidade):</label><br>
            <input type="text" name="origem_pagina" id="origem_pagina" maxlength="250"><br>

            <label for="feedback">Mensagem:</label><br>
            <input type="text" name="feedback" id="feedback" maxlength="100"><br>

           <div align="center"><button type="submit" class="botao">Enviar Feedback</button></div>   
            </div>
        </form>

    </div>
</div>

</body>
</html>
