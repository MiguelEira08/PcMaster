<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

$id = $_SESSION['user_id'] ?? 0;

if ($id > 0) {
    $sql = "SELECT * FROM utilizadores WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $utilizador = mysqli_fetch_assoc($result);
}

if (!isset($utilizador)) {
    $utilizador = [
        'id' => 0,
        'caminho_arquivo' => ''
    ];
}

if (!empty($utilizador['caminho_arquivo'])) {
    $foto = '../' . ltrim($utilizador['caminho_arquivo'], '/');
} else {
    $foto = '../imagens/user.png';
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
  <title>A minha conta</title>
    <link rel="stylesheet" href="../css/conta.css">
</head>
<body>
         <a href="../index/index.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
<div class="overlay">
<div class="content">

  <div class="caixa-container">

    <a href="gerir_conta.php" class="caixa">
      <img src="<?php echo $foto; ?>" alt="imagem" class="foto-perfil">
      Gerir Conta
    </a>

    <a href="estado_encomenda.php" class="caixa">
      <img src="../imagens/compra.png" alt="imagem" class="caixa-imagem">
      Estado da Encomenda
    </a>

  </div>

  <div class="caixa-container">

    <a href="enviar_feedback.php" class="caixa">
      <img src="../imagens/feedback.png" alt="imagem" class="caixa-imagem">
      Enviar Feedback
    </a>

    <a href="ver_feedback.php" class="caixa">
      <img src="../imagens/feedback.png" alt="imagem" class="caixa-imagem">
      Ver Feedback
    </a>

  </div>

  <div class="caixa-container">

    <a href="pedido_agenda.php" class="caixa">
      <img src="../imagens/agendar.png" alt="imagem" class="caixa-imagem">
      Pedido de Agendamento 
</a>

    <a href="gerir_agendamento.php" class="caixa">
      <img src="../imagens/gerir_agenda.png" alt="imagem" class="caixa-imagem">
      Ver Agendamento
    </a>

  </div>

  <div class="caixa-container">

  <a href="../favoritos/meus_favoritos.php" class="caixa">
    <img src="../imagens/favoritos.png" alt="imagem" class="caixa-imagem">
    Favoritos
  </a>

</div>

</div>
</div>
</div>

</body>
</html>
