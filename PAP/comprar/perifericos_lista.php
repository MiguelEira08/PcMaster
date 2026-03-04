<?php
session_start();
    include_once '../db.php';
      $tipoSelecionado = isset($_GET['tipo']) ? $_GET['tipo'] : null;
      $qSelecionado = isset($_GET['q']) ? $_GET['q'] : null;

      $sql = "SELECT * FROM perifericos";
      $conditions = [];
      $params = [];
      $types = "";

      if ($tipoSelecionado) {
        $conditions[] = "tipo = ?";
        $params[] = $tipoSelecionado;
        $types .= "s";
      }

      if ($qSelecionado) {
        $conditions[] = "(marca LIKE ? OR nome LIKE ?)";
        $qLike = "%" . $qSelecionado . "%";
        $params[] = $qLike;
        $params[] = $qLike;
        $types .= "ss";
      }

      if (count($conditions) > 0) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
      }

      if (count($params) > 0) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
      } else {
        $result = mysqli_query($conn, "SELECT * FROM perifericos");
      }

      if ($result && mysqli_num_rows($result) > 0) {
        echo '<div class="grid-produtos" style="display: flex; flex-wrap: wrap; gap: 24px; justify-content: flex-start;">';
        while($row = mysqli_fetch_assoc($result)) {
$ja_favorito = false;
if (isset($_SESSION['user_id'])) {
    $sqlFav = "SELECT id FROM favoritos 
               WHERE id_utilizador = ? 
               AND id_item = ? 
               AND tipo_item = 'periferico'";
    $stmtFav = $conn->prepare($sqlFav);
    $stmtFav->bind_param("ii", $_SESSION['user_id'], $row['id']);
    $stmtFav->execute();
    $resFav = $stmtFav->get_result();
    $ja_favorito = $resFav->num_rows > 0;
}
    $precoOriginal = $row["preco"];
    $desconto = $row["desconto"];
    $inicio = $row["tempoinicio_desconto"];
    $fim = $row["tempofim_desconto"];
    $agora = time();
    $inicioTime = $inicio ? strtotime($inicio) : null;
    $fimTime = $fim ? strtotime($fim) : null;
          echo '<div class="cartao-produto" style="width:300px; height:400px; display:flex; flex-direction:column; justify-content:space-between; box-sizing:border-box;">';
          echo '<div class="cartao-imagem" style="position:relative; width:100%; height:250px; display:flex; align-items:center; justify-content:center; overflow:hidden;">';
                  if (isset($_SESSION['user_id'])) {
    echo '<span class="coracao '.($ja_favorito ? 'ativo' : '').'" 
            data-id="'.$row['id'].'" 
            data-tipo="periferico"
            style="position:absolute; top:10px; right:10px; font-size:22px; cursor:pointer; z-index:20;">
            ❤
          </span>';
}
           if ($desconto !== null && $desconto > 0 && $inicioTime && $fimTime && $agora >= $inicioTime && $agora <= $fimTime) {
          echo '<div style="position:absolute; top:10px; left:10px; background:burlywood; color:black; padding:6px 10px; font-weight:bold; border-radius:6px; font-size:14px; z-index:10;">-' . (int)$desconto . '%</div>';
}
            echo '<img src="../imagens/'. $row["caminho_arquivo"] .'" alt="Imagem de '. htmlspecialchars($row["nome"]) .'" style="max-width:100%; max-height:100%; object-fit:contain;">';
            echo '</div>';
            echo '<div class="cartao-detalhes" style="flex:1; display:flex; flex-direction:column; justify-content:space-between; align-items:center;">';
            echo '<div class="manrope-titulo" style="text-align:center;">'. htmlspecialchars($row["nome"]) .'</div>';
if ($desconto !== null && $desconto > 0 && $inicioTime && $fimTime && $agora >= $inicioTime && $agora <= $fimTime) {
    $precoComDesconto = $precoOriginal - ($precoOriginal * ($desconto / 100));
    echo '<div style="text-align:center;">';
    echo '<div class="contador" data-fim="'.$fim.'"></div>';
    echo '<span style="color:red; text-decoration:line-through;">€'. number_format($precoOriginal, 2) .'</span><br>';
    echo '<span style="color:green; font-weight:bold;">€'. number_format($precoComDesconto, 2) .'</span>';
    echo '</div>';
} else {
    echo '<h4 class="preco" style="margin:5px 0 10px 0;">€'. number_format($precoOriginal, 2) .'</h4>';
}

echo '<a href="produto_periferico.php?id='.$row["id"].'" class="btn-adicionar">Visualizar</a>';            
echo '</div>';
echo '</div>';
        }
        echo '</div>';
      } else {
        echo '<p class="nao-encontrado">Nenhum periférico encontrado.</p>';
      }
    
      ?>
