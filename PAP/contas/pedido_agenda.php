<?php
ob_start();
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/Exception.php';
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

// Verificação de segurança da sessão
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

$id_utilizador = $_SESSION['user_id'];
$erro = '';
$sucesso = '';
$redir = false;

// Buscar dados do utilizador para o e-mail
$stmt = $conn->prepare("SELECT nome, email FROM utilizadores WHERE id = ?");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$utilizador = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_agendamento = $_POST['data_agendamento'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fim = $_POST['hora_fim'] ?? '';
    $tipo_servico = $_POST['tipo_servico'] ?? '';
    $localidade = trim($_POST['localidade'] ?? '');

    // Array de serviços permitidos
    $servicos_permitidos = ['montagem', 'reparação'];

    // Validações
    if (empty($data_agendamento) || empty($hora_inicio) || empty($hora_fim) || empty($tipo_servico) || empty($localidade)) {
        $erro = 'Todos os campos são obrigatórios!';
    } elseif (!in_array($tipo_servico, $servicos_permitidos)) {
        $erro = 'O tipo de serviço selecionado é inválido.';
    } elseif ($hora_inicio >= $hora_fim) {
        $erro = 'A hora de fim deve ser posterior à hora de início.';
    } elseif (strtotime($data_agendamento . ' ' . $hora_inicio) < time()) {
        $erro = 'Não é possível agendar para uma data ou hora no passado.';
    } else {
        // 1. Inserir na base de dados com as novas colunas
        $stmt = $conn->prepare("INSERT INTO agendamentos (utilizador_id, data_agendamento, hora_inicio, hora_fim, tipo_servico, localidade) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $id_utilizador, $data_agendamento, $hora_inicio, $hora_fim, $tipo_servico, $localidade);

        if ($stmt->execute()) {
            $sucesso = 'Agendamento realizado com sucesso! A redirecionar...';
            $redir = true;
            
            // 2. Enviar e-mail de notificação (PHPMailer)
            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'pcmastergeral@gmail.com'; 
                $mail->Password   = 'iuiv lkdy abyt xojv'; // ⚠️ ATUALIZA A TUA PASSWORD AQUI
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                $mail->addAddress('migueleira08@gmail.com', 'Administrador');
                $mail->addAddress('gustavofigueiredo.a.f@gmail.com', 'Administrador');
                
                $mail->isHTML(true);
                
                $servico_formatado = mb_convert_case($tipo_servico, MB_CASE_TITLE, "UTF-8");
                $mail->Subject = "Novo Agendamento: - PcMaster";
                
                // Formatação das horas e data para o e-mail
                $data_formatada = date('d/m/Y', strtotime($data_agendamento));
                $hora_i_formatada = date('H:i', strtotime($hora_inicio));
                $hora_f_formatada = date('H:i', strtotime($hora_fim));

                // Variáveis sanitizadas para segurança
                $nome_seguro = htmlspecialchars($utilizador['nome']);
                $email_seguro = htmlspecialchars($utilizador['email']);
                $localidade_segura = htmlspecialchars($localidade);

                $body = "
                <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                        
                        <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);'>Novo Agendamento!</h1>
                        </div>
                        
                        <div style='padding: 30px;'>
                            <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Novo pedido de agendamento recebido.</p>
                            
                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Dados do Cliente</h3>
                            <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0; margin-bottom: 25px;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Nome:</strong> {$nome_seguro}</p>
                                <p style='margin: 0; font-size: 14px; color: #333333;'><strong>Email:</strong> <a href='mailto:{$email_seguro}' style='color: #0056b3; text-decoration: none;'>{$email_seguro}</a></p>
                            </div>

                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Detalhes do Serviço</h3>
                            <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0; margin-bottom: 25px;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Tipo de Serviço:</strong> <span style='background-color: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 13px; font-weight: bold;'>{$servico_formatado}</span></p>
                                <p style='margin: 0; font-size: 14px; color: #333333;'><strong>Localidade:</strong> {$localidade_segura}</p>
                            </div>

                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Disponibilidade Sugerida</h3>
                            <div style='background-color: #eef5ff; border: 1px solid #cce0ff; padding: 20px; border-radius: 6px;'>
                                <p style='margin: 0 0 10px 0; font-size: 15px; color: #004085;'><strong>Data:</strong> {$data_formatada}</p>
                                <p style='margin: 0; font-size: 15px; color: #004085;'><strong>Horário:</strong> Das {$hora_i_formatada} às {$hora_f_formatada}</p>
                            </div>
                        </div>
                        
                        <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                            <p style='margin: 0; font-size: 14px; color: #888888;'><strong style='color: burlywood;'>PcMaster</strong></p>
                        </div>
                        
                    </div>
                </div>
                ";

                $mail->Body = $body;
                $mail->send();
            } catch (Exception $e) {
                error_log("Falha ao enviar email de agendamento: {$mail->ErrorInfo}");
            }
        } else {
            $erro = 'Erro ao processar o agendamento na base de dados.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title>Marcar Agendamento</title>
    <link rel="stylesheet" href="../css/conta.css">
    <style>
        .text-center { text-align: center; }
        .mt-2 { margin-top: 10px; }
        .mb-2 { margin-bottom: 10px; }
        
        /* Estilos adicionais para organizar as horas lado a lado */
        .row-horas {
            display: flex;
            gap: 15px;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .col-hora {
            flex: 1;
        }
        .col-hora input {
            width: 100%;
            box-sizing: border-box; /* Garante que o padding não quebre a largura */
        }
    </style>
</head>
<body>
    <a href="javascript:history.back()" class="botao-voltar voltar-fixo">← Voltar</a>

<div class="bg">
    <div class="overlay"></div>

    <div class="content">
        <form method="POST" class="form-conta">
            <?php if ($erro): ?>
                <p class="error-message"><?= htmlspecialchars($erro) ?></p>
            <?php elseif ($sucesso): ?>
                <p class="success-message"><?= htmlspecialchars($sucesso) ?></p>
                <?php if ($redir): ?>
                    <script>
                        setTimeout(function () {
                            window.location.href = 'conta.php';
                        }, 1500);
                    </script>
                <?php endif; ?>
            <?php endif; ?>

            <div class="text-center mb-2">
                <h2>Marcar Agendamento</h2>
                <p style="color: white;">Preencha os dados para solicitar um serviço</p>
            </div>

            <label for="data_agendamento">Data pretendida</label>
            <input type="date" id="data_agendamento" name="data_agendamento" required>
            <br><br>

            <div class="row-horas">
                <div class="col-hora">
                    <label for="hora_inicio">Hora de Início</label>
                    <input type="time" id="hora_inicio" name="hora_inicio" required>
                </div>
                <div class="col-hora">
                    <label for="hora_fim">Hora de Fim</label>
                    <input type="time" id="hora_fim" name="hora_fim" required>
                </div>
            </div>

            <label for="tipo_servico">Tipo de Serviço</label>
            <select id="tipo_servico" name="tipo_servico" required style="width: 100%; padding: 10px; border-radius: 5px; margin-top: 5px;">
                <option value="">Selecione uma opção...</option>
                <option value="montagem">Montagem de Equipamento</option>
                <option value="reparação">Reparação / Manutenção</option>
            </select>
            <br><br>

            <label for="localidade">Localidade</label>
            <input type="text" id="localidade" name="localidade" placeholder="Ex: Rua da Saudade, 123, Aveiro" required>
            <br>

            <div class="text-center mt-2">
                <button type="submit" class="botao">Confirmar Marcação</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>