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
            $listaProdutos = "<ul>";
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
                $listaProdutos .= "<li>{$prod_info['nome']} - {$item['quantidade']} x €" . number_format($prod_info['preco'], 2, ',', '.') . "</li>";
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
                $mail->Password   = 'mjsv oxar shbz dfzp';         
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                $mail->addAddress($cliente_email, $cliente_nome);

                $mail->isHTML(true);
                $mail->Subject = 'Compra Efetuada';

                $mail->Body = "
                    <h2>Olá, {$cliente_nome}!</h2>
                    <p>Obrigado pela sua compra. Abaixo os detalhes:</p>
                    <h4>Produtos:</h4>
                    $listaProdutos
                    <p><strong>Total:</strong> €" . number_format($total, 2, ',', '.') . "</p>
                    <p><strong>Estado:</strong> Pendente</p>
                    <p>Obrigado por comprar na PcMaster. \n Volte Sempre!</p>
                ";
                $mail->send();

                $mail->CharSet = 'UTF-8';
                $mail->clearAddresses();
                $mail->addAddress('migueleira08@gmail.com', 'Miguel');

                $mail->Subject = 'Compra Realizada';
                $mail->Body = "
                    <h2>Nova encomenda recebida</h2>
                    <p>Cliente: {$cliente_nome} ({$cliente_email})</p>
                    <p><strong>Produtos:</strong></p>
                    $listaProdutos
                    <p><strong>Total:</strong> €" . number_format($total, 2, ',', '.') . "</p>
                    <p><strong>Estado:</strong> Pendente</p>
                    <p>Para mais informações acede ao site</p>
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
