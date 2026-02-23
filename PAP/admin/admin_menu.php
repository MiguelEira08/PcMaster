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
    <title>Painel de Administração - Menu</title>
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
        <h1>Gestão do Menu</h1>

        <a href="../admin_gestao/adicionar_menu.php" class="btn criar" style="margin-left:10px;">
            Adicionar Menu
        </a>

        <div class="table-container">
            <table id="tabela" class="datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Link</th>
                        <th>Tipo</th>
                        <th>Ordem</th>
                        <th>Ativo</th>
                        <th>Editar / Apagar</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT * FROM menu ORDER BY ordem ASC";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {

                        echo '<tr id="linha-' . $row['id_menu'] . '">';
                        echo '<td>' . $row['id_menu'] . '</td>';
                        echo '<td>' . htmlspecialchars($row['Nome']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['link']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['tipo']) . '</td>';
                        echo '<td>' . (int)$row['ordem'] . '</td>';
                        echo '<td>' . ($row['ativo'] ? 'Sim' : 'Não') . '</td>';

                        echo '<td class="acoes">';
                        echo '  <a href="../admin_gestao/editar_menu.php?id=' . $row['id_menu'] . '" class="btn editar">Editar</a>';
                        echo '  <a href="#" class="btn remover btn-remover" data-id="' . $row['id_menu'] . '">Apagar</a>';
                        echo '</td>';

                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">Nenhum item de menu encontrado.</td></tr>';
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
$(document).ready(function(){
    $('.btn-remover').click(function(){
        const botao = $(this);
        const id = botao.data('id');

        if(confirm('Tem a certeza que quer apagar este item do menu?')) {
            $.post('../admin_gestao/remover_menu.php', { id: id }, function(resposta){
                if(resposta.trim() === 'ok'){
                    botao.closest('tr').remove();
                } else {
                    alert('Erro ao apagar o item do menu.');
                }
            });
        }
    });
});
</script>

</body>
</html>
