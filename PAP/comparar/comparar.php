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
    <a href="comparar_componentes.php" class="caixa">
      <img src="../imagens/componentes.avif" alt="imagem" class="caixa-imagem">Comparar Componentes</a>

    <a href="comparar_perifericos.php" class="caixa">
      <img src="../imagens/perifericos.png" alt="imagem" class="caixa-imagem">Comparar Periféricos</a>
  </div>
</div>

</div>
</div>  
</body>
</html>