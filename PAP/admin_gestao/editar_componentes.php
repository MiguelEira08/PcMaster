<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}

$erro = '';
$sucesso = '';

$marcas = [
  'Intel','AMD','Nvidia','Asus','MSI','Gigabyte','Zotac','ASRock',
  'Kingston','G.SKILL','Corsair','Mars Gaming','SeaSonic','Razer',
  'Fractal Design','LianLi','Thermalright','Noctua','Arctic'
];

$tipos = [
  'placa grafica', 'processador', 'motherboard',
  'memoria RAM', 'armazenamento', 'fonte de alimentaçao',
  'cooler de cpu', 'ventoinha', 'gabinete'
];
$stock = filter_input(INPUT_GET, 'stock', FILTER_SANITIZE_NUMBER_INT);
$tipo  = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$marca = filter_input(INPUT_GET, 'marca', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$queryFiltros = http_build_query([
    'stock' => $stock ?? '',
    'tipo'  => $tipo ?? '',
    'marca' => $marca ?? '',
]);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $erro = 'ID inválido.';
} else {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM componentes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $erro = 'Componente não encontrado.';
    } else {
        $componente = $result->fetch_assoc();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome']);
            $preco = floatval($_POST['preco']);
            $descricao = trim($_POST['descricao']);
            $stock = intval($_POST['stock']);
            $marca = $_POST['marca'];
            $tipo = $_POST['tipo'];
            $imagemAtual = $componente['caminho_arquivo'];
            $novaImagem = $imagemAtual;
            $desconto = $_POST['desconto'] !== '' ? floatval($_POST['desconto']) : null;
            $inicio = $_POST['tempoinicio_desconto'] !== '' ? $_POST['tempoinicio_desconto'] : null;
            $fim = $_POST['tempofim_desconto'] !== '' ? $_POST['tempofim_desconto'] : null;

            if (!empty($_FILES['imagem']['name'])) {
                $tmp = $_FILES['imagem']['tmp_name'];
                $nomeOriginal = basename($_FILES['imagem']['name']);
                $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (in_array($extensao, $permitidas)) {
                    $nomeFinal = uniqid() . "." . $extensao;
                    $destino = '../imagens/' . $nomeFinal;
                    if (move_uploaded_file($tmp, $destino)) {
                        // remove anterior
                        if (file_exists('../imagens/' . $imagemAtual)) {
                            unlink('../imagens/' . $imagemAtual);
                        }
                        $novaImagem = $nomeFinal;
                    } else {
                        $erro = 'Falha ao guardar nova imagem.';
                    }
                } else {
                    $erro = 'Formato de imagem inválido.';
                }
            }

            if (empty($erro)) {
                $stmt = $conn->prepare("UPDATE componentes SET nome=?, preco=?, descricao=?, caminho_arquivo=?, stock=?, marca=?, tipo=?, desconto=?, tempoinicio_desconto=?, tempofim_desconto=? WHERE id=?");
                $stmt->bind_param("sdssissdssi", $nome, $preco, $descricao, $novaImagem, $stock, $marca, $tipo,$desconto,$inicio,$fim,$id);
                if ($stmt->execute()) {
                    header("Location: ../admin/admin_componentes.php?" . $queryFiltros . "#linha-" . $id);
                    exit;
                } else {
                    $erro = 'Erro ao atualizar componente.';
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
  <title>Editar Componente</title>
  <link rel="stylesheet" href="../css/admin_criar.css">
</head>
<body>
        <a href="javascript:history.back()" class="botao-voltar voltar-fixo">
    ← Voltar
</a>

<div class="bg">
  <div class="overlay"></div>
  <div class="content">
    <form method="POST" enctype="multipart/form-data">
        <h1>Editar Componente</h1>
<br>
        <?php if ($erro): ?>
            <p class="error-message"><?= htmlspecialchars($erro) ?></p>
        <?php elseif ($sucesso): ?>
            <p class="success-message"><?= htmlspecialchars($sucesso) ?></p>
        <?php endif; ?>

        <?php if (isset($componente)): ?>
            <label>Nome</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($componente['nome']) ?>" required>
<br><br>  
            <label>Preço (€)</label>
            <input type="number" step="0.01" name="preco" value="<?= htmlspecialchars($componente['preco']) ?>" required>
<br><br>  
            <label>Descrição</label>
            <textarea 
                name="descricao" 
                id="descricao"
                rows="6"
                placeholder="Faz uma descrição do componente"
                style="resize: none; width: 100%; height: 120px; font-family: inherit; font-size: 1rem; padding: 8px; border-radius: 4px; border: 1px solid #ccc;"
                required
            ><?= htmlspecialchars($componente['descricao']) ?></textarea>
<br><br>  
            <label>Stock</label>
            <input type="number" name="stock" value="<?= htmlspecialchars($componente['stock']) ?>" required>
<br><br>  
            <label>Marca</label>
            <select name="marca" required>
              <?php foreach ($marcas as $m): ?>
                <option value="<?= $m ?>" <?= ($componente['marca'] === $m ? 'selected' : '') ?>><?= $m ?></option>
              <?php endforeach; ?>
            </select>
<br><br>  
            <label>Tipo</label>
            <select name="tipo" required>
              <?php foreach ($tipos as $t): ?>
                <option value="<?= $t ?>" <?= ($componente['tipo'] === $t ? 'selected' : '') ?>><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select>
<br><br>  
            <label>Desconto (%)</label>
<input type="number" name="desconto" step="0.01" min="0" max="100"
value="<?= htmlspecialchars($componente['desconto']) ?>">
<br><br>  
<label>Início do desconto</label>
<input type="datetime-local" name="tempoinicio_desconto"
value="<?= $componente['tempoinicio_desconto'] ? date('Y-m-d\TH:i', strtotime($componente['tempoinicio_desconto'])) : '' ?>">
<br><br>  
<label>Fim do desconto</label>
<input type="datetime-local" name="tempofim_desconto"
value="<?= $componente['tempofim_desconto'] ? date('Y-m-d\TH:i', strtotime($componente['tempofim_desconto'])) : '' ?>">

            <center>
            <label>Imagem atual</label><br>
            <img src="../imagens/<?= htmlspecialchars($componente['caminho_arquivo']) ?>" alt="Atual" style="width: 100%; max-width: 200px; margin-bottom: 12px; border-radius: 8px;">
            </center>
            <label>Alterar imagem</label>
            <input type="file" name="imagem" accept="image/*">
            <br><br>
           <div align="center"><button type="submit" class="botao">Guardar alterações</button></div> 
        <?php endif; ?>
    </form>
  </div>
</div>
</body>
</html>
