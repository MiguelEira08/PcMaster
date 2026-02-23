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
      <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiko:wght@600&display=swap" rel="stylesheet">
</head>
<body>  
    <a href="../index/index.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
    <div class= "bg">
    <div class="overlay">
    <div class="content">
  <div class="caixa-container">
    <a href="componentes.php" class="caixa">
      <img src="../imagens/componentes.avif" alt="imagem" class="caixa-imagem" class="amiko-semibold">Comprar Componentes</a>
    
    <a href="perifericos.php" class="caixa">
      <img src="../imagens/perifericos.png" alt="imagem" class="caixa-imagem" class="amiko-semibold">Comprar Periféricos</a>
  </div>
</div>
  <div class="caixa-container">
 

</div>
</div>


</body>
</html>