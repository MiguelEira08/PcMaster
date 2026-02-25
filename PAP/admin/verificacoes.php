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
    <title>Painel de Administração - Verificação de Utilizadores</title>
    <link rel="stylesheet" href="../css/admin_produto.css">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.dataTables.css" />
</head>
<body>
        <a href="../admin/admin_utilizadores.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
 <div class="overlay"></div>
  <br><br><br>
  <div class="content">

    <h1>Gestão de Verificação de Utilizadores</h1>

<div class="table-container">
   <table id="tabela" class="datatable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Foto</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Telefone</th>
        <th>Tipo</th>
        <th>Verificada</th>
        <th>Data Criação</th>
        <th>Código</th>
        <th>Ações</th>
      </tr>
    </thead>
<tbody>
<?php
$result = mysqli_query($conn, "
    SELECT *
    FROM verificacao_utilizadores
    WHERE tipo IN ('admin', 'utilizador')
    ORDER BY id DESC
");

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        echo '<tr>';
        echo "<td>{$row['id']}</td>";

        $foto = !empty($row['caminho_arquivo']) ? "../{$row['caminho_arquivo']}" : "../imagens/user.png";
        echo "<td><img src='$foto' style='width:50px; height:50px; border-radius:50%; object-fit:cover;'></td>";

        echo "<td>{$row['nome']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['numtel']}</td>";
        echo "<td>{$row['tipo']}</td>";
        echo "<td>{$row['Verificada']}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['duracao'])) . "</td>";
        echo '<td>' . $row['codigo_verificacao'] . '</td>';   
        echo '<td class="acoes">';
       
        echo '<button class="btn remover" onclick="removerVerificacao(' . $row['id'] . ', this)">Apagar</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="10">Nenhum utilizador encontrado.</td></tr>';
}
?>
    </tbody>
  </table>


</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
<script src="scriptadmin.js"></script>

<script>
  function removerVerificacao(id, btn) {
    if (confirm('Tem a certeza que deseja remover esta verificação?')) {
      fetch('../admin_gestao/remover_verificacao.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
      })
      .then(response => response.text())
      .then(text => {
        if (text.trim() === 'ok') {
          let table = $(btn).closest('table').DataTable();
          table.row($(btn).parents('tr')).remove().draw();
        } else {
          alert(text);
        }
      })
      .catch(() => alert('Erro na remoção.'));
    }
  }
</script>

</body>
</html>
