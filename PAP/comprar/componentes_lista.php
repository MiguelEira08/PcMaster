<?php
session_start();

include_once '../db.php';

$tipoSelecionado = isset($_GET['tipo']) ? strtolower(trim($_GET['tipo'])) : '';
$busca           = isset($_GET['q'])    ? trim($_GET['q'])             : '';

$sql    = "SELECT * FROM componentes WHERE 1=1";
$params = [];
$types  = '';

if ($tipoSelecionado !== '') {
    $sql .= " AND LOWER(tipo) = ?";
    $params[] = $tipoSelecionado;
    $types .= 's';
}

if ($busca !== '') {
    $sql .= " AND LOWER(nome) LIKE LOWER(CONCAT('%', ?, '%'))";
    $params[] = $busca;
    $types .= 's';
}

$sql .= " ORDER BY nome ASC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, $sql);
}

if ($result && mysqli_num_rows($result) > 0) {
    echo '<div class="grid-produtos" style="display:flex; flex-wrap:wrap; gap:24px; justify-content:flex-start;">';
    while ($row = mysqli_fetch_assoc($result)) {
        $ja_favorito = false;

if (isset($_SESSION['user_id'])) {
    $sqlFav = "SELECT id FROM favoritos 
               WHERE id_utilizador = ? 
               AND id_item = ? 
               AND tipo_item = 'componente'";
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
            data-tipo="componente"
            style="position:absolute; top:10px; right:10px; font-size:22px; cursor:pointer; z-index:20;">
            ❤
          </span>';
}
         if ($desconto !== null && $desconto > 0 && $inicioTime && $fimTime && $agora >= $inicioTime && $agora <= $fimTime) {
          echo '<div style="position:absolute; top:10px; left:10px; background:burlywood; color:black; padding:6px 10px; font-weight:bold; border-radius:6px; font-size:14px; z-index:10;">-' . (int)$desconto . '%</div>';
}
            echo '<img src="../imagens/' . $row['caminho_arquivo'] . '" alt="Imagem de ' . htmlspecialchars($row['nome']) . '" style="max-width:100%; max-height:100%; object-fit:contain;">';
            echo '</div>';
            echo '<div class="cartao-detalhes" style="flex:1; display:flex; flex-direction:column; justify-content:space-between; align-items:center;">';
            echo '<div class="manrope-titulo" style="text-align:center;">' . htmlspecialchars($row['nome']) . '</div>';
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
                echo '<a href="produto_componente.php?id=' . $row['id'] . '" class="btn-adicionar" style="margin-bottom:10px;">Visualizar</a>';
            echo '</div>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo '<h3 class="nao-encontrado">Nenhum componente encontrado.</h3>';
}
?>
<script>
document.querySelectorAll('.contador').forEach(contador => {
    const fim = new Date(contador.dataset.fim).getTime();

    function atualizar() {
        const agora = new Date().getTime();
        const distancia = fim - agora;

        if (distancia <= 0) {
            contador.innerHTML = "Promoção terminada";
            return;
        }

        const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
        const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));

        contador.innerHTML = "Termina em: " + dias + "d " + horas + "h " + minutos + "m";
    }

    atualizar();
    setInterval(atualizar, 60000);
});
</script>