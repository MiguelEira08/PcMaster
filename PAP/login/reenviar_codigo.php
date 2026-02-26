<?php
require_once '../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';
require_once '../PHPMailer/Exception.php';

$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if (!$email) {
    die("Pedido inválido.");
}

// Verificar se a conta ainda existe
$stmt = $conn->prepare("
    SELECT nome FROM verificacao_utilizadores WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Conta não encontrada ou já verificada.");
}

// Gerar novo código
$novo_codigo = random_int(100000, 999999);

// Atualizar código e tempo
$stmt = $conn->prepare("
    UPDATE verificacao_utilizadores
    SET codigo_verificacao = ?, duracao = NOW()
    WHERE email = ?
");
$stmt->bind_param("ss", $novo_codigo, $email);
$stmt->execute();
$stmt->close();

/* =====================
   ENVIAR EMAIL
===================== */

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
    $mail->addAddress($email, $user['nome']);
    $mail->isHTML(true);
    $mail->Subject = 'Código de verificação - PcMaster';

    $mail->Body = "
    <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            
            <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>Equipa PcMaster</h1>
            </div>
            
            <div style='padding: 30px; text-align: center;'>
                <p style='font-size: 18px; margin-top: 0; color: #333333;'>Olá, <strong>{$user['nome']}</strong>!</p>
                <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Recebemos um pedido para gerar um novo código de verificação para a sua conta.</p>
                
                <div style='background-color: #f8f9fa; border: 2px dashed burlywood; padding: 20px; margin: 30px 0; border-radius: 10px;'>
                    <p style='margin: 0 0 10px 0; font-size: 14px; color: #888888; text-transform: uppercase; letter-spacing: 1px;'>O seu código é:</p>
                    <h1 style='margin: 0; font-size: 36px; letter-spacing: 8px; color: #0056b3; font-family: monospace;'>{$novo_codigo}</h1>
                </div>

                <div style='background-color: #fff9e6; border-left: 4px solid #ffcc00; padding: 12px; margin-top: 25px; border-radius: 0 4px 4px 0; text-align: left;'>
                    <p style='margin: 0; font-size: 14px; color: #856404;'>
                        <strong>Atenção:</strong> Este código é válido por apenas <strong>10 minutos</strong>.
                    </p>
                </div>
                
                <p style='font-size: 14px; line-height: 1.6; margin-top: 30px; color: #888888;'>Se não solicitou este código, por favor ignore este e-mail.</p>
            </div>
            
            <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                    <p style='margin: 0; font-size: 14px; color: #888888;'>Obrigado por escolher a <strong style='color: burlywood;'>PcMaster</strong>!</p>
            </div>
        </div>
    </div>
    ";

    $mail->send();

} catch (Exception $e) {
    die("Erro ao enviar email.");
}

// Voltar para a página de verificação
header("Location: verificar_codigo.php?email=" . urlencode($email));
exit();
?>