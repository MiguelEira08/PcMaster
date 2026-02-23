<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/loja.css">
    <title>Loja</title>
    <link rel="icon" type="image/png" href="../imagens/icon.png">
</head>
<body>  
       <a href="../index/index.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
    <div class= "bg">
    <div class="overlay">
    <div class="content">
  <div class="caixa-container">
    <a href="sobre_nos.php" class="caixa">
      <img src="../imagens/sobre_nos.png" alt="imagem" class="caixa-imagem">Sobre Nós</a>

    <a href="suporte.php" class="caixa">
      <img src="../imagens/suporte.png" alt="imagem" class="caixa-imagem">Suporte</a>
  </div>
</div>
  <div class="caixa-container">
  
</div>
</div>


</body>
</html>