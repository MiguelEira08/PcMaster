<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}
$stock = filter_input(INPUT_GET, 'stock', FILTER_SANITIZE_NUMBER_INT);
$tipo  = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$marca = filter_input(INPUT_GET, 'marca', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$queryFiltros = http_build_query([
    'stock' => $stock ?? '',
    'tipo'  => $tipo ?? '',
    'marca' => $marca ?? '',
]);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração - Periféricos</title>
    <link rel="stylesheet" href="../css/admin_produto.css">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.dataTables.css" />
</head>
<body>
                   <a href="../admin/admin_dashboard.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>
    <br><br><br>
    <div class="content">
        <h1>Gestão de Periféricos</h1>
            <a href="../admin_gestao/adicionar_perifericos.php" class="btn criar">Adicionar Periféricos</a>

        <div class="table-container">
        <table id="tabela" class="datatable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Marca</th>
                    <th>Preço (€)</th>
                    <th>Stock</th>
                    <th>Desconto (%)</th>
                    <th>Imagem</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql    = "SELECT * FROM perifericos WHERE 1=1";
                $params = [];
                $types  = '';

                if ($stock !== null && $stock !== '') {
                    $sql     .= " AND stock = ?";
                    $params[]  = (int)$stock;
                    $types    .= 'i';
                }

                if ($tipo !== null && $tipo !== '') {
                    $sql     .= " AND LOWER(tipo) LIKE LOWER(CONCAT('%', ?, '%'))";
                    $params[]  = $tipo;
                    $types    .= 's';
                }

                if ($marca !== null && $marca !== '') {
                    $sql     .= " AND marca = ?";
                    $params[]  = $marca;
                    $types    .= 's';
                }

                $sql .= " ORDER BY id DESC";

                if ($params) {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = mysqli_query($conn, $sql);
                }

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr id="linha-' . $row['id'] . '">';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . htmlspecialchars($row['nome']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['tipo']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['marca']) . '</td>';
                        echo '<td>' . number_format($row['preco'], 2, ',', '.') . '</td>';
                        echo '<td>' . $row['stock'] . '</td>';
                        echo '<td>' . ($row['desconto'] ? $row['desconto'] . '%' : '-') . '</td>';
                        echo '<td><img src="../imagens/' . htmlspecialchars($row['caminho_arquivo']) . '" width="60" alt="Imagem do periférico"></td>';
                        echo '<td class="acoes">';
                        echo '  <a href="../admin_gestao/editar_perifericos.php?id=' . $row['id'] . '&' . $queryFiltros . '" class="btn editar">Editar</a>';
                        echo '  <a href="#" class="btn remover" onclick="removerPeriferico(' . $row['id'] . '); return false;">Apagar</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8">Nenhum periférico encontrado.</td></tr>';
                }
                ?>
                </tbody>
            </table>
      
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
<script src="scriptadmin.js"></script>
<script>
function removerPeriferico(id) {
    if (confirm('Tem a certeza que deseja remover este periférico?')) {
        $.ajax({
            url: '../admin_gestao/remover_periferico.php',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                if (response.trim() === 'ok') {
                    $('#linha-' + id).fadeOut();
                } else {
                    alert('Erro ao remover periférico.');
                }
            },
            error: function() {
                alert('Erro na requisição.');
            }
        });
    }
}
</script>

</body>
</html>
