<?php
session_start();
include_once __DIR__ . '/../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
            
include_once __DIR__ . '/../PHPMailer/PHPMailer.php';
include_once __DIR__ . '/../PHPMailer/SMTP.php';
include_once __DIR__ . '/../PHPMailer/Exception.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit;
}
$id_utilizador = (int) $_SESSION['user_id'];

$distritos = [
    'Aveiro','Beja','Braga','Bragança','Castelo Branco','Coimbra','Évora','Faro',
    'Guarda','Leiria','Lisboa','Portalegre','Porto','Santarém','Setúbal',
    'Viana do Castelo','Vila Real','Viseu','Madeira','Açores'
];
$erros = [];

if (isset($_POST['confirmar_compra'])) {
    $rua           = trim($_POST['rua'] ?? '');
    $distrito      = trim($_POST['distrito'] ?? '');
    $codigo_postal = trim($_POST['codigo_postal'] ?? '');
    $numero_cartao = preg_replace('/\s+/', '', ($_POST['numero_cartao'] ?? ''));

    if ($rua === '' || $distrito === '' || $codigo_postal === '' || $numero_cartao === '') {
        $erros[] = 'Todos os campos são obrigatórios.';
    }
    if ($codigo_postal && !preg_match('/^[0-9]{4}-[0-9]{3}$/', $codigo_postal)) {
        $erros[] = 'Formato de código-postal inválido. Use NNNN-NNN.';
    }
    if ($numero_cartao && !preg_match('/^[0-9]{13,19}$/', $numero_cartao)) {
        $erros[] = 'Número de cartão inválido (13-19 dígitos).';
    }

    if (!$erros) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("
                SELECT id AS carrinho_id, tipo_produto, id_produto, quantidade
                FROM carrinho
                WHERE id_utilizador = ?
                FOR UPDATE
            ");
            $stmt->bind_param("i", $id_utilizador);
            $stmt->execute();
            $itens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (!$itens) {
                throw new Exception("Carrinho vazio. Não é possível finalizar a compra.");
            }

            $hash_cartao = password_hash($numero_cartao, PASSWORD_DEFAULT);
            $estado = "Pendente";

            $stmtCab = $conn->prepare("
                INSERT INTO fim_compra (utilizador_id, rua, distrito, codigo_postal, numero_cartao, estado)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtCab->bind_param("isssss", $id_utilizador, $rua, $distrito, $codigo_postal, $hash_cartao, $estado);
            $stmtCab->execute();
            $compra_id = $stmtCab->insert_id;
            $stmtCab->close();

            $stmtItem = $conn->prepare("
                INSERT INTO fim_compra_itens (compra_id, nome_produto, tipo_produto, produto_id, quantidade, preco)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $total = 0;
            $listaProdutos = "<ul style='padding-left: 20px; margin: 0;'>";
            foreach ($itens as $item) {
                $tabela = ($item['tipo_produto'] === 'componente') ? 'componentes' : 'perifericos';

                $stProd = $conn->prepare("SELECT nome, preco, stock FROM $tabela WHERE id = ? FOR UPDATE");
                $stProd->bind_param("i", $item['id_produto']);
                $stProd->execute();
                $prod_info = $stProd->get_result()->fetch_assoc();
                $stProd->close();

                if (!$prod_info) {
                    throw new Exception("Produto ID {$item['id_produto']} não encontrado.");
                }
                if ($prod_info['stock'] < $item['quantidade']) {
                    throw new Exception("Stock insuficiente para o produto: {$prod_info['nome']}.");
                }

                $stUpd = $conn->prepare("UPDATE $tabela SET stock = stock - ? WHERE id = ?");
                $stUpd->bind_param("ii", $item['quantidade'], $item['id_produto']);
                $stUpd->execute();
                $stUpd->close();

                $stmtItem->bind_param("issiid", 
                    $compra_id,
                    $prod_info['nome'],
                    $item['tipo_produto'],
                    $item['id_produto'],
                    $item['quantidade'],
                    $prod_info['preco']
                );
                $stmtItem->execute();

                $subtotal = $prod_info['preco'] * $item['quantidade'];
                $total += $subtotal;
                $listaProdutos .= "<li style='margin-bottom: 5px;'>{$prod_info['nome']} - <strong>{$item['quantidade']} unid.</strong> x €" . number_format($prod_info['preco'], 2, ',', '.') . "</li>";
            }
            $stmtItem->close();
            $listaProdutos .= "</ul>";

            $stDel = $conn->prepare("DELETE FROM carrinho WHERE id_utilizador = ?");
            $stDel->bind_param("i", $id_utilizador);
            $stDel->execute();
            $stDel->close();

            $conn->commit();

            $stUser = $conn->prepare("SELECT nome, email FROM utilizadores WHERE id = ?");
            $stUser->bind_param("i", $id_utilizador);
            $stUser->execute();
            $user_info = $stUser->get_result()->fetch_assoc();
            $stUser->close();

            $cliente_nome  = $user_info['nome'];
            $cliente_email = $user_info['email'];

            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';     
                $mail->SMTPAuth   = true;
                $mail->Username   = 'pcmastergeral@gmail.com'; 
                $mail->Password   = 'mjsv oxar shbz dfzp'; // ⚠️ ATUALIZA AQUI! E MUDA A TUA PASS DA CONTA GOOGLE.       
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $total_formatado = number_format($total, 2, ',', '.');

                // ==========================================
                // 1. EMAIL PARA O CLIENTE
                // ==========================================
                $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                $mail->addAddress($cliente_email, $cliente_nome);
                $mail->isHTML(true);
                $mail->Subject = 'Compra Efetuada - PcMaster';

                $mail->Body = "
                <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                        
                        <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);'>Equipa PcMaster</h1>
                        </div>
                        
                        <div style='padding: 30px;'>
                            <p style='font-size: 16px; margin-top: 0;'>Olá, <strong>{$cliente_nome}</strong>,</p>
                            <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Obrigado pela sua compra! A sua encomenda foi registada e está agora a ser processada pela nossa equipa.</p>
                            
                            <h3 align='center' style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Estado da Encomenda</h3>
                            <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 6px; text-align: center; margin: 25px 0;'>
                                <p style='margin: 0; font-size: 16px; color: #555555;'><strong style='font-size: 18px; text-transform: uppercase; color: #dc3545;'>Pendente</strong></p>
                            </div>
                            
                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Resumo da Encomenda</h3>
                            
                            <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; line-height: 1.5;'><strong>Produtos:</strong><br><br> {$listaProdutos}</p>
                                <hr style='border: none; border-top: 1px solid #dddddd; margin: 15px 0;'>
                                <p style='margin: 0; font-size: 16px;'><strong>Total Pago:</strong> <strong style='color: #333333;'>€ {$total_formatado}</strong></p>
                            </div>
                        </div>
                        
                        <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                    <p style='margin: 0; font-size: 14px; color: #888888;'>Obrigado por escolher a <strong style='color: burlywood;'>PcMaster</strong>!</p>
                        </div>
                        
                    </div>
                </div>
                ";
                $mail->send();

                $mail->clearAddresses();
                $mail->addAddress('migueleira08@gmail.com', 'Administrador');
                $mail->addAddress('gustavofigueiredo.a.f@gmail.com', 'Administrador'); 

                $mail->Subject = 'Encomenda efetuada - PcMaster';
                $mail->Body = "
                <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9; padding: 30px 15px; color: #333333;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                        
                        <div style='background-color: burlywood; padding: 25px; text-align: center;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);'>Nova Encomenda!</h1>
                        </div>
                        
                        <div style='padding: 30px;'>
                            <p style='font-size: 15px; line-height: 1.6; color: #555555;'>Uma nova encomenda foi realizada.</p>
                            
                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Dados do Cliente</h3>
                            <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0; margin-bottom: 25px;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Nome:</strong> {$cliente_nome}</p>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Email:</strong> <a href='mailto:{$cliente_email}' style='color: #0056b3; text-decoration: none;'>{$cliente_email}</a></p>
                                <p style='margin: 0; font-size: 14px; color: #333333;'><strong>Estado Atual:</strong> <span style='color: #dc3545; font-weight: bold;'>Pendente</span></p>
                            </div>

                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Dados de Envio</h3>
                            <div style='background-color: #f8f9fa; border-left: 4px solid burlywood; padding: 15px; border-radius: 0 4px 4px 0; margin-bottom: 25px;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Rua:</strong> {$rua}</p>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: #333333;'><strong>Código Postal:</strong> {$codigo_postal}</p>
                                <p style='margin: 0; font-size: 14px; color: #333333;'><strong>Distrito:</strong> {$distrito}</p>
                            </div>

                            <h3 style='color: burlywood; margin: 30px 0 15px 0; font-size: 18px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px;'>Produtos Encomendados</h3>
                            <div style='background-color: #eef5ff; border: 1px solid #cce0ff; padding: 20px; border-radius: 6px;'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; line-height: 1.5; color: #004085;'>{$listaProdutos}</p>
                                <hr style='border: none; border-top: 1px solid #cce0ff; margin: 15px 0;'>
                                <p style='margin: 0; font-size: 16px; color: #004085;'><strong>Total:</strong> <strong>€ {$total_formatado}</strong></p>
                            </div>
                        </div>
                        
                        <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                            <p style='margin: 0; font-size: 14px; color: #888888;'><strong style='color: burlywood;'>PcMaster</strong></p>
                        </div>
                        
                    </div>
                </div>
                ";
                $mail->send();

            } catch (Exception $e) {
                error_log("Erro ao enviar email: " . $mail->ErrorInfo);
            }

            header('Location: carrinho.php?compra_ok=1');
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $erros[] = $e->getMessage();
        }
    }
} else {
    $rua = $distrito = $codigo_postal = '';
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
  <title>Finalizar Compra</title>
  <link rel="stylesheet" href="../css/comprar.css">
</head>
<body>
    <a href="javascript:history.back()" class="botao-voltar voltar-fixo">← Voltar</a>
<div class="bg">
  <div class="overlay"></div>
  <div class="content">
        <form method="POST">
          <h1>Finalizar Compra</h1>

          <?php if ($erros): ?>
            <ul class="error-message">
              <?php foreach ($erros as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
<br>
          <label>Rua:</label>
          <input type="text" name="rua" value="<?= htmlspecialchars($rua ?? '') ?>" required><br>

          <label>Distrito:</label>
          <select name="distrito" required>
            <option value="">Seleciona o teu Distrito</option>
            <?php foreach ($distritos as $d): ?>
              <option value="<?= $d ?>" <?= (($distrito ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select><br>

          <label>Código Postal:</label>
          <input type="text" name="codigo_postal" placeholder="1234-567"
                 value="<?= htmlspecialchars($codigo_postal ?? '') ?>" required><br>

          <label>Número do Cartão:</label>
          <input type="text" name="numero_cartao" placeholder="Somente dígitos" required>
            <br>
           <div align="center"><button type="submit" name="confirmar_compra" class="botao">Finalizar compra</button></div> 
        </form>
    
  </div>
</div>
</body>
</html>