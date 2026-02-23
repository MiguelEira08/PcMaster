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
                $mail->Password   = 'mjsv oxar shbz dfzp';     
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                $mail->addAddress($email, $nome_utilizador);
                $mail->isHTML(true);
                $mail->Subject = "Resposta do Feedback";

                $mail->Body = "
                    <h2>Olá, {$nome_utilizador}</h2>
                    <p>Recebemos o seu feedback e agradecemos o seu contacto.</p>
                    <hr>
                    <p><strong>Motivo:</strong> {$motivo}</p>
                    <p><strong>Origem:</strong> " . ($origem ?: 'Não especificada') . "</p>
                    <p><strong>Mensagem enviada por si:</strong><br>" . nl2br(htmlspecialchars($feedback_texto)) . "</p>
                    <hr>
                    <p><strong>Resposta do administrador:</strong><br>" . nl2br(htmlspecialchars($mensagem_resposta)) . "</p>

                    <br>
                    <p>Agradecemos pelo seu feedback acerca dos erros que deixamos passar despercebido.</p>
                    <p>Atenciosamente,<br><strong>{$admin_nome}</strong> - Equipa PcMaster</p>
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
           <div align="center"><button type="submit" class="botao">Enviar Resposta</button></div> 

                </form>
            <?php else: ?>
                <p>Feedback não encontrado ou inválido.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
