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
    <title>Gestão de Bloqueio de Utilizadores</title>
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

    <h1>Estado das Contas dos Utilizadores</h1>

<div class="table-container">
   <table id="tabela" class="datatable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Foto</th>
        <th>Nome</th>
        <th>Tentativas</th>
        <th>Estado da conta</th>
        <th>Último Login</th>
        <th>Último Logout</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $result = mysqli_query($conn, " SELECT u.id, u.nome, u.caminho_arquivo, us.tentativas, us.bloqueado, us.ultimo_login, us.ultimo_logout FROM utilizador_seguranca us INNER JOIN utilizadores u ON u.id = us.utilizador_id ORDER BY u.id DESC");

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

            echo '<tr>';
            echo "<td>{$row['id']}</td>";

            $foto = !empty($row['caminho_arquivo']) ? "../{$row['caminho_arquivo']}" : "../imagens/user.png";
            echo "<td><img src='$foto' style='width:50px; height:50px; border-radius:50%; object-fit:cover;'></td>";

            echo "<td>{$row['nome']}</td>";
            echo "<td>{$row['tentativas']}</td>";

            $estado = ($row['bloqueado'] === 'sim') 
                ? "<span style='color:red;'>Bloqueado</span>" 
                : "<span style='color:green;'>Ativo</span>";

            echo "<td>{$estado}</td>";
            echo "<td>{$row['ultimo_login']}</td>";
            echo "<td>{$row['ultimo_logout']}</td>";

            echo '<td class="acoes">';
            echo ' <a href="../admin_gestao/alterar_estado.php?id=' . $row['id'] . '" class="btn editar" style="margin: 10px 0;">Editar</a>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7">Nenhum registo encontrado.</td></tr>';
    }
    ?>
    </tbody>
  </table>


</div

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
<script src="scriptadmin.js"></script>

</body>
</html>
