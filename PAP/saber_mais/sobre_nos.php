<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($utilizador)) {
    $utilizador = ['id' => 0]; 
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="../css/saber_mais.css">
     <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiko:wght@600&display=swap" rel="stylesheet">
    <title>Suporte</title>
</head>
<body>
           <a href="../saber_mais/saber_mais.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>

    <div class="bg">
    <div class="overlay"></div>
    <div class="content">
<big><big><big><big><big><big><big><big>Sobre nós</big></big></big></big></big></big></big></big>

<div style="width:100%; height:5px; background-color: burlywood; margin:20px 0;"></div>

<big><big><big><big>PcMaster - Componentes e Periféricos</big></big></big></big>

<p>A PcMaster é uma loja online, para facilitar a compra de componentes e periféricos de Computadores</p>

<p>Criada a ideia em finais de 2024, Miguel Eira e Gustavo Figueiredo juntaram dois projetos que se conectavam e desenvolveram a PcMaster, tendo como objetivo facilitar a decisão e a acessibilidade dos compradores com os produtos desejados</p>

<p>Somos mentes criativas em constante funcionamento, mas mais do que ter ideias, gostamos de as executar. Sempre prontos a abraçar novos desafios, garantimos inovação e simplicidade: a chave para o sucesso.</p>

<div class="perfis-container">
    <div class="perfil">
        <a href="gustavo_info.php">
        <img src="../imagens/Gustavo.webp" alt="Gustavo" class="caixa-imagem">
        <div>Gustavo Figueiredo</div>
        </a>
    </div>

    <div class="perfil">
        <a href="miguel_info.php">
        <img src="../imagens/Miguel.png" alt="Miguel" class="caixa-imagem">
        <div>Miguel Eira</div>
        </a>
    </div>
</div>
  </div>
</div>
</div>
</body>
</html>