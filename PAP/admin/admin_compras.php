<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}
$userId  = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);
$estadoF = filter_input(INPUT_GET, 'estado',  FILTER_SANITIZE_FULL_SPECIAL_CHARS);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração – Compras</title>
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
        <h1>Compras por Utilizador</h1>
        <div class="table-container"> 
        <table id="tabela" class="datatable">
            <thead>
                

                <tr>
                    <th>Utilizador</th>
                    <th>Email</th>
                    <th>ID Compra</th>
                    <th>Data</th>
                    <th>Total (€)</th>
                    <th>Estado</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT fc.id AS compra_id,
                           fc.data_compra,
                           fc.estado,
                           u.id   AS user_id,
                           u.nome AS user_nome,
                           u.email AS user_email,
                           COALESCE(SUM(fci.preco * fci.quantidade), 0) AS total
                    FROM fim_compra fc
                    JOIN utilizadores u ON u.id = fc.utilizador_id
                    LEFT JOIN fim_compra_itens fci ON fci.compra_id = fc.id
                    WHERE 1 = 1";
            $params = [];
            $types  = '';

            if ($userId !== null && $userId !== '') {
                $sql     .= " AND u.id = ?";
                $params[]  = (int)$userId;
                $types    .= 'i';
            }
            if ($estadoF !== null && $estadoF !== '') {
                $sql     .= " AND fc.estado = ?";
                $params[]  = $estadoF;
                $types    .= 's';
            }

            $sql .= " GROUP BY fc.id
                      ORDER BY u.nome ASC, fc.data_compra DESC";

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
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['user_nome'])  . '</td>';
                    echo '<td>' . htmlspecialchars($row['user_email']) . '</td>';
                    echo '<td>' . $row['compra_id'] . '</td>';
                    echo '<td>' . date('d/m/Y H:i', strtotime($row['data_compra'])) . '</td>';
                    echo '<td>' . number_format($row['total'], 2, ',', '.') . '</td>';
                    echo '<td>' . htmlspecialchars($row['estado']) . '</td>';
                    echo '<td><a class="btn editar" href="detalhes_compra.php?id=' . $row['compra_id'] . '">Ver</a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="7">Nenhuma compra encontrada.</td></tr>';
            }
            ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
<script>
$(document).ready(function(){
    new DataTable('#tabela', {
        order: [[3, 'desc']], // ordena por Data
        language: {
           url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/pt-PT.json'
        }
    });
});
</script>
</body>
</html>
