<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/admin_dash.css">
    <title>Painel de Administração - Utilizadores</title>
    <link rel="icon" type="image/png" href="../imagens/icon.png">
</head>
<body>
                 <a href="../admin/admin_dashboard.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
    <div class= "bg">
    <div class="overlay"></div>
    <br><br>
    <div class="content">
  <div class="caixa-container">
    <a href="verificacoes.php" class="caixa">
      <img src="../imagens/verificacao.png" alt="Comprar" class="caixa-imagem">Gerir Verificações</a>

    <a href="gerir_utilizadores.php" class="caixa">
      <img src="../imagens/user.png" alt="Comprar" class="caixa-imagem">Gerir Utilizadores</a>
    
    <a href="bloqueios.php" class="caixa">
      <img src="../imagens/block.png" alt="Comprar" class="caixa-imagem">Gerir Bloqueios</a>
  </div>
</div>
</div>
</body>
</html>