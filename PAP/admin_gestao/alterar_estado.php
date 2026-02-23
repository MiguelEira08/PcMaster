<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

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

    $stmt = $conn->prepare("
        SELECT u.nome, u.caminho_arquivo, us.bloqueado
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
        <a href="javascript:history.back()" class="botao-voltar voltar-fixo">
    ← Voltar
</a>


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

        <img src="../<?= $utilizador['caminho_arquivo'] ?>" 
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
        <div align="center">
            <button type="submit" class="botao">Alterar Estado</button>
        </div>

        <br>


    <?php endif; ?>

  </form>

 </div>
</div>

</body>
</html>
