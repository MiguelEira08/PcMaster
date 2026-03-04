<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once '../db.php';
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';
require_once '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$feedback_texto = '';
$nome_utilizador = '';
$data_envio = '';
$motivo = '';
$origem = '';
$feedback_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $feedback_id = (int) $_GET['id'];

    $stmt = $conn->prepare("
        SELECT f.feedback, f.Motivo, f.origem_pagina, u.nome AS utilizador_nome, f.data_envio 
        FROM feedback f 
        JOIN utilizadores u ON f.user_id = u.id 
        WHERE f.id = ?
    ");
    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $stmt->bind_result($feedback_texto, $motivo, $origem, $nome_utilizador, $data_envio);

    if (!$stmt->fetch()) {
        $feedback_texto = null;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder'])) {
    $feedback_id = (int) $_POST['feedback_id'];
    $mensagem_resposta = trim($_POST['mensagem']);
    $data_resposta = date('Y-m-d H:i:s');

    if (isset($_SESSION['user_id']) && !empty($mensagem_resposta)) {
        $stmt = $conn->prepare("SELECT nome FROM utilizadores WHERE id = ? AND tipo = 'admin'");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($admin_nome);
        $stmt->fetch();
        $stmt->close();

        $stmt = $conn->prepare("
            SELECT f.feedback, f.Motivo, f.origem_pagina, u.nome, u.email 
            FROM feedback f 
            JOIN utilizadores u ON f.user_id = u.id 
            WHERE f.id = ?
        ");
        $stmt->bind_param("i", $feedback_id);
        $stmt->execute();
        $stmt->bind_result($feedback_texto, $motivo, $origem, $nome_utilizador, $email);
        $stmt->fetch();
        $stmt->close();

        if ($email) {
            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'pcmastergeral@gmail.com'; 
                $mail->Password   = 'iuiv lkdy abyt xojv';     
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                $mail->addAddress($email, $nome_utilizador);
                $mail->isHTML(true);
                $mail->Subject = "Resposta do Feedback - PcMaster";
                $mail->Body = "
<div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
        
        <div style='background-color: burlywood; padding: 25px; text-align: center;'>
            <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>Equipa PcMaster</h1>
        </div>
        
        <div style='padding: 30px;'>
            <p style='font-size: 16px; margin-top: 0;'>Olá, <strong>{$nome_utilizador}</strong>,</p>
            <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Recebemos o seu feedback e agradecemos imenso o seu contacto.</p>
            
            <div style='background-color: #f8f9fa; border-left: 4px solid #adb5bd; padding: 15px; margin: 25px 0; border-radius: 0 4px 4px 0;'>
                <p style='margin: 0 0 8px 0; font-size: 14px;'><strong>Motivo:</strong> {$motivo}</p>
                <p style='margin: 0 0 8px 0; font-size: 14px;'><strong>Origem:</strong> " . ($origem ?: 'Não especificada') . "</p>
                <p style='margin: 0; font-size: 14px; color: #666666;'><strong>A sua mensagem:</strong><br><br>" . nl2br(htmlspecialchars($feedback_texto)) . "</p>
            </div>
            
            <h3 style='color: #0056b3; margin: 30px 0 15px 0; font-size: 18px;'>Resposta do Administrador:</h3>
            
            <div style='background-color: #eef5ff; border: 1px solid #cce0ff; padding: 20px; border-radius: 6px;'>
                <p style='margin: 0; font-size: 15px; line-height: 1.6; color: #004085;'>" . nl2br(htmlspecialchars($mensagem_resposta)) . "</p>
            </div>

            <p style='font-size: 15px; line-height: 1.6; margin-top: 30px; color: #555555;'>Agradecemos pelo seu feedback. É com a sua ajuda que conseguimos melhorar!</p>
        </div>
        
        <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
            <p style='margin: 0; font-size: 14px; color: #888888;'>Atenciosamente,<br><strong style='color: #333333;'>{$admin_nome}</strong> - Equipa PcMaster</p>
        </div>
    </div>
</div>
";
                $mail->send();
            } catch (Exception $e) {
                error_log("Erro ao enviar o email de resposta ao utilizador: " . $mail->ErrorInfo);
            }
        }

        $stmt = $conn->prepare("
            INSERT INTO respostas_admin (feedback_id, nome_admin, resposta_admin, data_envio)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $feedback_id, $admin_nome, $mensagem_resposta, $data_resposta);
        $stmt->execute();
        $stmt->close();

        $status = "lida";
        $stmt = $conn->prepare("UPDATE feedback SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $feedback_id);
        $stmt->execute();
        $stmt->close();

        header("Location: feedback_cliente.php");
        exit;
    } else {
        exit("Admin não autenticado ou mensagem vazia.");
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Responder Feedback</title>
    <link rel="stylesheet" href="../css/admin_dash.css">
</head>
<body>
                <a href="javascript:history.back()" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>
    <div class="content">
        <div class="admin-container">
            <h1>Responder ao Feedback</h1>
<br>
            <?php if (!empty($feedback_texto)): ?>
                <p><strong>Utilizador:</strong> <?= htmlspecialchars($nome_utilizador) ?></p>
                <br>
                <p><strong>Data:</strong> <?= htmlspecialchars($data_envio) ?></p>
                <br>
                <p><strong>Motivo:</strong> <?= htmlspecialchars($motivo) ?></p>
                <br>
                <p><strong>Origem:</strong> <?= htmlspecialchars($origem) ?></p>
                <br>
                <p><strong>Mensagem:</strong><?= nl2br(htmlspecialchars($feedback_texto)) ?></p>

                <form method="post" style="margin-top: 20px;">
                    <input type="hidden" name="feedback_id" value="<?= $feedback_id ?>">
                    <label for="mensagem"><strong>Sua resposta:</strong></label><br>
                    <textarea name="mensagem" id="mensagem" required rows="6" style="width: 100%; border-radius: 12px; padding: 10px; background-color: rgba(255, 255, 255, 0.8);"></textarea><br><br>
            <br>
         <div align="center"><button type="submit" name="responder" class="botao">Enviar Resposta</button></div>

                </form>
            <?php else: ?>
                <p>Feedback não encontrado ou inválido.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
