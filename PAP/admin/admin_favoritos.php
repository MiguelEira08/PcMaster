<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração - Favoritos</title>
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
<h1>Gestão de Favoritos</h1>

<div class="table-container">
<table id="tabela" class="datatable">
<thead>
<tr>
<th>ID</th>
<th>Utilizador</th>
<th>Email</th>
<th>ID Produto</th>
<th>Tipo</th>
<th>Data</th>
<th>Ação</th>
</tr>
</thead>
<tbody>

<?php
$sql = "SELECT f.*, u.nome, u.email
        FROM favoritos f
        JOIN utilizadores u ON f.id_utilizador = u.id
        ORDER BY f.id DESC";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        echo '<tr id="linha-'.$row['id'].'">';
        echo '<td>'.$row['id'].'</td>';
        echo '<td>'.htmlspecialchars($row['nome']).'</td>';
        echo '<td>'.htmlspecialchars($row['email']).'</td>';
        echo '<td>'.$row['id_item'].'</td>';
        echo '<td>'.htmlspecialchars($row['tipo_item']).'</td>';
        echo '<td>'.$row['data_adicionado'].'</td>';
        echo '<td class="acoes">';
        echo '<a href="#" class="btn remover btn-remover" data-id="'.$row['id'].'">Apagar</a>';
        echo '</td>';
        echo '</tr>';
    }

} else {
    echo '<tr><td colspan="7">Nenhum favorito encontrado.</td></tr>';
}
?>

</tbody>
</table>
</div>
</div>
</div>

<script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>

<script>
$(document).ready(function(){
    $('#tabela').DataTable();
});

$(document).on('click', '.btn-remover', function(){
    const botao = $(this);
    const id = botao.data('id');

    if(confirm('Tem certeza que quer remover este favorito?')) {
        $.post('remover_favorito.php', { id: id }, function(resposta){
            if(resposta.trim() === 'ok'){
                botao.closest('tr').fadeOut();
            } else {
                alert('Erro ao remover favorito.');
            }
        });
    }
});
</script>

</body>
</html>