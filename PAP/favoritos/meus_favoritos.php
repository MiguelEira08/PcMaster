<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id_utilizador = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Os Meus Favoritos</title>
  <link rel="stylesheet" href="../css/comprar.css">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<a href="../contas/conta.php" class="botao-voltar voltar-fixo">
← Voltar
</a>

<div class="bg">
<div class="overlay"></div>

<div class="loja-container">

<!-- SIDEBAR -->
<aside class="sidebar">
  <h3 style="text-align:center;"> Meus Favoritos</h3>
  <ul>
    <li><a href="meus_favoritos.php">Todos</a></li>
    <li><a href="meus_favoritos.php?tipo=componente">Componentes</a></li>
    <li><a href="meus_favoritos.php?tipo=periferico">Periféricos</a></li>
  </ul>
</aside>

<!-- CONTENT -->
<main class="content">

<?php

$tipoFiltro = $_GET['tipo'] ?? '';

$sql = "SELECT * FROM favoritos WHERE id_utilizador = ?";
$params = [$id_utilizador];
$types = "i";

if ($tipoFiltro != '') {
    $sql .= " AND tipo_item = ?";
    $params[] = $tipoFiltro;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

echo '<div class="grid-produtos" style="display:flex; flex-wrap:wrap; gap:24px;">';

while ($fav = $result->fetch_assoc()) {

    if ($fav['tipo_item'] === 'componente') {
        $query = $conn->prepare("SELECT * FROM componentes WHERE id = ?");
    } else {
        $query = $conn->prepare("SELECT * FROM perifericos WHERE id = ?");
    }

    $query->bind_param("i", $fav['id_item']);
    $query->execute();
    $produto = $query->get_result()->fetch_assoc();

    if ($produto) {

        echo '<div class="cartao-produto" style="width:300px;">';

        echo '<div class="cartao-imagem" style="position:relative;height:250px;display:flex;align-items:center;justify-content:center;">';

        echo '<span class="coracao ativo"
                data-id="'.$produto['id'].'"
                data-tipo="'.$fav['tipo_item'].'"
                style="position:absolute;top:10px;right:10px;font-size:22px;cursor:pointer;">
                ❤
              </span>';

        echo '<img src="../imagens/'.$produto['caminho_arquivo'].'" 
              style="max-width:100%;max-height:100%;object-fit:contain;">';

        echo '</div>';

        echo '<div class="cartao-detalhes" style="text-align:center;">';
        echo '<div class="manrope-titulo">'.$produto['nome'].'</div>';
        echo '<h4 class="preco">€'.number_format($produto['preco'],2).'</h4>';
        echo '<div align="center"><a href="../comprar/produto_'.$fav['tipo_item'].'.php?id='.$produto['id'].'" class="btn-adicionar">Visualizar</a></div>';
        echo '</div>';

        echo '</div>';
    }
}

echo '</div>';

} else {
    echo '<h3 class="nao-encontrado">Ainda não adicionaste favoritos.</h3>';
}

?>

</main>
</div>
</div>

<script>

document.addEventListener('click', function(e) {

  if (e.target.classList.contains('coracao')) {

    const idItem = e.target.dataset.id;
    const tipoItem = e.target.dataset.tipo;

    fetch('toogle_favoritos.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'id_item=' + idItem + '&tipo_item=' + tipoItem
    })
    .then(res => res.json())
    .then(data => {

      if (data.status === 'removido') {
        e.target.closest('.cartao-produto').remove();
      }

    });

  }

});

</script>

</body>
</html>