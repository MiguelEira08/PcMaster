<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

// Importações do PHPMailer
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';
require_once '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}

$erro = '';
$sucesso = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $erro = 'ID inválido.';
} else {
    $id = (int) $_GET['id'];

    // Obter dados do utilizador alvo
    $stmt = $conn->prepare("
        SELECT u.nome, u.email, u.caminho_arquivo, us.bloqueado
        FROM utilizadores u
        LEFT JOIN utilizador_seguranca us ON us.utilizador_id = u.id
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $erro = 'Utilizador não encontrado.';
    } else {
        $utilizador = $result->fetch_assoc();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $novoEstado = ($utilizador['bloqueado'] === 'sim') ? 'nao' : 'sim';

            $conn->query("
                INSERT IGNORE INTO utilizador_seguranca (utilizador_id, tentativas, bloqueado)
                VALUES ($id, 0, 'nao')
            ");

            $stmt = $conn->prepare("
                UPDATE utilizador_seguranca
                SET bloqueado = ?
                WHERE utilizador_id = ?
            ");
            $stmt->bind_param("si", $novoEstado, $id);
            $stmt->execute();
            $stmt->close();

            $sucesso = "Estado atualizado com sucesso!";
            $utilizador['bloqueado'] = $novoEstado;

            // ---- INÍCIO DO ENVIO DE EMAIL ----
            if (!empty($utilizador['email'])) {
                
                // 1. Obter o nome do administrador atual
                $admin_nome = 'Administrador'; // Valor por defeito
                if (isset($_SESSION['user_id'])) {
                    $stmt_admin = $conn->prepare("SELECT nome FROM utilizadores WHERE id = ? AND tipo = 'admin'");
                    $stmt_admin->bind_param("i", $_SESSION['user_id']);
                    $stmt_admin->execute();
                    $stmt_admin->bind_result($nome_db);
                    if ($stmt_admin->fetch()) {
                        $admin_nome = $nome_db;
                    }
                    $stmt_admin->close();
                }

                $mail = new PHPMailer(true);
                try {
                    $mail->CharSet = 'UTF-8';
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'pcmastergeral@gmail.com'; 
                    $mail->Password   = 'mjsv oxar shbz dfzp'; // Atualiza a password aqui!
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                    $mail->addAddress($utilizador['email'], $utilizador['nome']);
                    $mail->isHTML(true);
                    
                    if ($novoEstado === 'sim') {
                        $estado_titulo = "Conta Bloqueada";
                        $cor_destaque = "#dc3545"; // Vermelho para a caixa de estado
                        $mensagem_corpo = "A sua conta foi <strong>bloqueada</strong> temporariamente por unm administrador. Se deseja saber o motivo, por favor, entre em contacto connosco.";
                    } else {
                        $estado_titulo = "Conta Desbloqueada";
                        $cor_destaque = "#28a745"; // Verde para a caixa de estado
                        $mensagem_corpo = "A sua conta foi <strong>desbloqueada</strong>. Já pode aceder novamente a todos os nossos serviços sem restrições.";
                    }

                    $mail->Subject = "Estado da Conta - PcMaster";

                    $mail->Body = "
                    <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
                        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                            
                            <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);'>Equipa PcMaster</h1>
                            </div>
                            
                            <div style='padding: 30px;'>
                                <p style='font-size: 16px; margin-top: 0;'>Olá, <strong>{$utilizador['nome']}</strong>,</p>
                                <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Por motivos de segurança, decidimos alterar o estado da sua conta.</p>
                                
                                <div style='background-color: #f8f9fa; border-left: 4px solid {$cor_destaque}; padding: 15px; margin: 25px 0; border-radius: 0 4px 4px 0;'>
                                    <p style='margin: 0; font-size: 16px; color: #333333;'>Estado atual: <strong style='color: {$cor_destaque};'>{$estado_titulo}</strong></p>
                                </div>
                                
                                <div style='background-color: #eef5ff; border: 1px solid #cce0ff; padding: 20px; border-radius: 6px;'>
                                    <p style='margin: 0; font-size: 15px; line-height: 1.6; color: #004085;'>{$mensagem_corpo}</p>
                                </div>
                            </div>
                            
                            <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                    <p style='margin: 0; font-size: 14px; color: #888888;'>Obrigado por escolher a <strong style='color: burlywood;'>PcMaster</strong>!</p>
                            </div>
                            
                        </div>
                    </div>
                    ";
                    
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Erro ao enviar email de estado ao utilizador: " . $mail->ErrorInfo);
                }
            }
            // ---- FIM DO ENVIO DE EMAIL ----
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title>Alterar Estado da Conta</title>
    <link rel="stylesheet" href="../css/admin_criar.css">
</head>
<body>
    <a href="javascript:history.back()" class="botao-voltar voltar-fixo">← Voltar</a>

    <div class="bg">
        <div class="overlay"></div>
        <div class="content">

            <form method="POST">
                <h1>Alterar Estado da Conta</h1>

                <?php if ($erro): ?>
                    <p class="error-message"><?= htmlspecialchars($erro) ?></p>
                <?php elseif ($sucesso): ?>
                    <p class="success-message"><?= htmlspecialchars($sucesso) ?></p>
                <?php endif; ?>

                <?php if (isset($utilizador)): ?>

                    <img src="../<?= htmlspecialchars($utilizador['caminho_arquivo']) ?>" 
                         style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:2px solid #fff;">
                    <br>

                    <p style="font-size:18px; font-weight:bold; color:black;"><?= htmlspecialchars($utilizador['nome']) ?></p>
                    <br>
                    <label>Estado atual:</label>
                    <p style="font-size:18px;">
                        <?= $utilizador['bloqueado'] === 'sim' 
                            ? "<span style='color:red;'>Bloqueado</span>" 
                            : "<span style='color:green;'>Ativo</span>" ?>
                    </p>

                    <br>
                    <div style="display: flex; justify-content: center; width: 100%;">
                        <button type="submit" class="botao">Alterar Estado</button>
                    </div>
                    <br>

                <?php endif; ?>

            </form>

        </div>
    </div>
</body>
</html>