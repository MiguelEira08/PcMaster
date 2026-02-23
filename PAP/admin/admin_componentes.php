<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';


if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}

$stock = filter_input(INPUT_GET, 'stock', FILTER_SANITIZE_NUMBER_INT);
$tipo  = filter_input(INPUT_GET, 'tipo',  FILTER_SANITIZE_FULL_SPECIAL_CHARS);
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
    <title>Painel de Administração - Componentes</title>
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
        <h1>Gestão de Componentes</h1>      
            <a href="../admin_gestao/adicionar_componentes.php" class="btn criar">Adicionar Componentes</a>

        <div class="table-container">
           <table id="tabela" class="datatable">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Marca</th>
                        <th scope="col">Preço (€)</th>
                        <th scope="col">Stock</th>
                        <th scope="col">Desconto (%)</th>
                        <th scope="col">Imagem</th>
                        <th scope="col">Editar / Apagar</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql    = "SELECT * FROM componentes WHERE 1=1";
                $params = [];
                $types  = '';

                if ($stock !== null && $stock !== '') {
                    $sql      .= " AND stock = ?";
                    $params[]  = (int)$stock;
                    $types    .= 'i';
                }
                $tipo = isset($_GET['tipo']) ? strtolower(trim($_GET['tipo'])) : '';  
                if ($tipo !== '') {
                    $sql      .= " AND LOWER(tipo) = ?";
                    $params[]  = $tipo;
                    $types    .= 's';
                }

                if ($marca !== null && $marca !== '') {
                    $sql      .= " AND marca = ?";
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
                        echo '<tr id="linha-' . $row['id'] . '" data-id="' . $row['id'] . '">';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . htmlspecialchars($row['nome']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['tipo']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['marca']) . '</td>';
                        echo '<td>' . number_format($row['preco'], 2, ',', '.') . '</td>';
                        echo '<td>' . $row['stock'] . '</td>';
                        echo '<td>' . ($row['desconto'] ? $row['desconto'] . '%' : '-') . '</td>';

                        echo '<td><img src="../imagens/' . htmlspecialchars($row['caminho_arquivo']) . '" width="60" alt="Imagem do componente"></td>';
                        echo '<td class="acoes">';                                    
                        echo '  <a href="../admin_gestao/editar_componentes.php?id=' . $row['id'] . '&' . $queryFiltros . '#linha-' . $row['id'] . '" class="btn editar">Editar</a>';                    
                        echo '  <a href="#" class="btn remover btn-remover" data-id="' . $row['id'] . '">Apagar</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8">Nenhum componente encontrado.</td></tr>';
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
$(document).on('click', '.btn-remover', function(){
    const botao = $(this);
    const id = botao.data('id');

    if(confirm('Tem certeza que quer remover este componente?')) {
        $.post('../admin_gestao/remover_componente.php', { id: id }, function(resposta){
            if(resposta.trim() === 'ok'){
                botao.closest('tr').fadeOut();
            } else {
                alert('Erro ao remover o componente.');
            }
        });
    }
});
</script>
<script>
  document.getElementById('searchBtn').addEventListener('click', pesquisar);
  document.getElementById('searchInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') pesquisar();
  });

  function pesquisar() {
    const query = document.getElementById('searchInput').value.trim();

    if (query === '') {
      alert('Escreve algo para pesquisar.');
      return;
    }

    fetch(`componentes_lista.php?q=${encodeURIComponent(query)}`)
      .then(response => {
        if (!response.ok) throw new Error('Erro na pesquisa');
        return response.text();
      })
      .then(html => {
        document.getElementById('content').innerHTML = html;
      })
      .catch(err => {
        console.error(err);
        document.getElementById('content').innerHTML =
          '<p>Erro ao pesquisar componentes.</p>';
      });
  }

  window.addEventListener('DOMContentLoaded', () => {
    fetch('componentes_lista.php')
      .then(r => r.text())
      .then(html => {
        document.getElementById('content').innerHTML = html;
      });
  });
</script>

</body>
</html>
