<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';
require_once '../PHPMailer/Exception.php';

$compraId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$compraId) {
    echo '<p>ID da compra inválido.</p>';
    exit;
}

// Atualizar estado da compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compra_id'], $_POST['novo_estado'])) {
    $novoEstado   = $_POST['novo_estado'];
    $estadosValid = ['Pendente', 'A caminho', 'Entregue'];

    if (in_array($novoEstado, $estadosValid, true)) {
        $stmtUpd = $conn->prepare('UPDATE fim_compra SET estado = ? WHERE id = ?');
        $stmtUpd->bind_param('si', $novoEstado, $compraId);
        $stmtUpd->execute();
        $stmtUpd->close();

        // Buscar dados do usuário
        $stmtUser = $conn->prepare("SELECT u.nome, u.email, fc.data_compra 
                                    FROM fim_compra fc 
                                    JOIN utilizadores u ON fc.utilizador_id = u.id 
                                    WHERE fc.id = ?");
        $stmtUser->bind_param("i", $compraId);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result();

        if ($resUser->num_rows > 0) {
            $dados = $resUser->fetch_assoc();
            $userNome = $dados['nome'];
            $userEmail = $dados['email'];
            $dataCompra = date('d/m/Y H:i', strtotime($dados['data_compra']));
        }
        $stmtUser->close();

        // Buscar itens da compra
        $stmtItens = $conn->prepare("SELECT nome_produto, quantidade, preco FROM fim_compra_itens WHERE compra_id = ?");
        $stmtItens->bind_param("i", $compraId);
        $stmtItens->execute();
        $resItens = $stmtItens->get_result();

        $nomesProdutos = [];
        $total = 0;
        while ($item = $resItens->fetch_assoc()) {
            $nomesProdutos[] = $item['nome_produto'] . ' (x' . $item['quantidade'] . ')';
            $total += $item['quantidade'] * $item['preco'];
        }
        $stmtItens->close();
        $mail = new PHPMailer(true);
try {
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'pcmastergeral@gmail.com';
    $mail->Password   = 'iuiv lkdy abyt xojv'; // ⚠️ POR FAVOR, MUDA A TUA PASSWORD!
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
    $mail->addAddress($userEmail, $userNome);
    $mail->isHTML(true);
    $mail->Subject = "Atualização da Encomenda - PcMaster";

    $produtosStr = implode(', ', $nomesProdutos);
    $totalFormatado = number_format($total, 2, ',', '.');

    // Lógica para definir as cores consoante o estado da encomenda
    $estado_formatado = strtolower(trim($novoEstado)); // Passa tudo para minúsculas para evitar erros de formatação
    
    if ($estado_formatado === 'pendente') {
        $cor_texto_estado = '#dc3545'; // Vermelho
        $cor_fundo_estado = '#f8d7da'; // Fundo vermelho clarinho
        $cor_borda_estado = '#f5c6cb';
    } elseif ($estado_formatado === 'a caminho') {
        $cor_texto_estado = 'chocolate'; // Laranja
        $cor_fundo_estado = '#fff3cd'; // Fundo laranja/amarelo clarinho
        $cor_borda_estado = '#ffeeba';
    } elseif ($estado_formatado === 'entregue') {
        $cor_texto_estado = '#28a745'; // Verde
        $cor_fundo_estado = '#d4edda'; // Fundo verde clarinho
        $cor_borda_estado = '#c3e6cb';
    } else {
        // Cor padrão (ex: azul) caso haja um estado diferente
        $cor_texto_estado = '#004085'; 
        $cor_fundo_estado = '#cce5ff';
        $cor_borda_estado = '#b8daff';
    }

    $mail->Body = "
    <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            
            <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);'>Equipa PcMaster</h1>
            </div>
            
            <div style='padding: 30px;'>
                <p style='font-size: 16px; margin-top: 0;'>Olá, <strong>{$userNome}</strong>,</p>
                <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Temos novidades! O estado da sua encomenda foi atualizado.</p>
                <h3 align='center' style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Estado da Encomenda</h3>
                            
                <div style='background-color: {$cor_fundo_estado}; border: 1px solid {$cor_borda_estado}; padding: 15px; border-radius: 6px; text-align: center; margin: 25px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #555555;'><strong style='font-size: 18px; text-transform: uppercase; color: {$cor_texto_estado};'>{$novoEstado}</strong></p>
                </div>
                
                <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Resumo da Encomenda</h3>
                
                <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0;'>
                    <p style='margin: 0 0 10px 0; font-size: 14px; color: #666666;'><strong>Data da compra:</strong> {$dataCompra}</p>
                    <p style='margin: 0 0 10px 0; font-size: 14px; line-height: 1.5;'><strong>Produto(s):</strong> {$produtosStr}</p>
                    <p style='margin: 0; font-size: 15px;'><strong>Total:</strong> <strong style='color: burlywood;'>{$totalFormatado} €</strong></p>
                </div>

                <p style='font-size: 15px; line-height: 1.6; margin-top: 30px; color: #555555;'>Se tiver alguma dúvida sobre a sua encomenda, não hesite em responder a este email.</p>
            </div>
            
            <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
            <p style='margin: 0; font-size: 14px; color: #888888;'>Obrigado por escolher a <strong style='color: burlywood;'>PcMaster</strong>!</p>
            </div>
            
        </div>
    </div>
    ";

    $mail->send();
} catch (Exception $e) {
    error_log("Erro ao enviar email de encomenda: " . $mail->ErrorInfo);
}
    }

    $redirect = 'detalhes_compra.php?id=' . $compraId;
    if (isset($_GET['user_id'])) {
        $redirect .= '&user_id=' . (int)$_GET['user_id'];
    }
    header('Location: ' . $redirect);
    exit;
}

// Buscar detalhes da compra
$stmtCompra = $conn->prepare("SELECT fc.*, u.nome AS user_nome, u.email AS user_email
                              FROM fim_compra fc
                              JOIN utilizadores u ON u.id = fc.utilizador_id
                              WHERE fc.id = ?");
$stmtCompra->bind_param('i', $compraId);
$stmtCompra->execute();
$resultCompra = $stmtCompra->get_result();

if ($resultCompra->num_rows === 0) {
    echo '<p>Compra não encontrada.</p>';
    exit;
}
$compra = $resultCompra->fetch_assoc();
$stmtCompra->close();

// Buscar itens
$stmtItens = $conn->prepare("SELECT nome_produto, tipo_produto, quantidade, preco
                             FROM fim_compra_itens
                             WHERE compra_id = ?");
$stmtItens->bind_param('i', $compraId);
$stmtItens->execute();
$resultItens = $stmtItens->get_result();

$total = 0;
$itens = [];
while ($row = $resultItens->fetch_assoc()) {
    $row['subtotal'] = $row['quantidade'] * $row['preco'];
    $total += $row['subtotal'];
    $itens[] = $row;
}
$stmtItens->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Compra #<?php echo $compraId; ?></title>
    <link rel="stylesheet" href="../css/admin_produto.css">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body>
            <a href="javascript:history.back()" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>
    <br><br><br>
    <div class="content" style="max-width:900px;">

        <!-- Título -->
        <h1>Detalhes da Compra #<?php echo $compraId; ?></h1>

        <!-- Tabela de informações da compra -->
        <div class="table-container">
<table id="tabela_compra" class="admin-table">
    <thead>
        <tr align="center"><th>Definição</th><th align="center ">Dados</th></tr>
    </thead>
    <tbody>
        <tr><td>Utilizador</td><td><?php echo htmlspecialchars($compra['user_nome']); ?></td></tr>
        <tr><td>Email</td><td><?php echo htmlspecialchars($compra['user_email']); ?></td></tr>
        <tr><td>Data</td><td><?php echo date('d/m/Y H:i', strtotime($compra['data_compra'])); ?></td></tr>
        <tr><td>Estado</td><td>
            <form method="POST" class="estado-form" style="display:inline;">
                <input type="hidden" name="compra_id" value="<?php echo $compraId; ?>">
                <select name="novo_estado" onchange="this.form.submit()">
                    <?php
                    foreach (['Pendente', 'A caminho', 'Entregue'] as $opt) {
                        $sel = ($compra['estado'] === $opt) ? 'selected' : '';
                        echo '<option value="' . $opt . '" ' . $sel . '>' . $opt . '</option>';
                    }
                    ?>
                </select>
            </form>
        </td></tr>
        <tr><td>Endereço</td><td><?php echo htmlspecialchars($compra['rua'] . ', ' . $compra['distrito'] . ' (' . $compra['codigo_postal'] . ')'); ?></td></tr>
    </tbody>
</table>
        </div>

        <br>

        <!-- Tabela de itens da compra -->
        <div class="table-container">
   <table id="tabela_itens" class="admin-table">
    <thead>
        <tr>
            <th>Produto</th>
            <th>Tipo</th>
            <th>Quantidade</th>
            <th>Preço (€)</th>
            <th>Subtotal (€)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($itens as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['nome_produto']) ?></td>
            <td><?= htmlspecialchars(ucfirst($item['tipo_produto'])) ?></td>
            <td><?= $item['quantidade'] ?></td>
            <td><?= number_format($item['preco'], 2, ',', '.') ?></td>
            <td><?= number_format($item['subtotal'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
            <td><strong><?= number_format($total, 2, ',', '.') ?></strong></td>
        </tr>
    </tfoot>
</table>
</div>    
    </div>
</div>

<!-- Inicialização DataTables -->
<script>
$(document).ready(function(){
    $('#tabela_compra').DataTable({
        paging: false,
        searching: false,
        info: false
    });

    $('#tabela_itens').DataTable({
        paging: true,
        searching: true,
        info: true,
        order: [[0, 'asc']]
    });
});
</script>

<script src="scriptadmin.js"></script>
</body>
</html>