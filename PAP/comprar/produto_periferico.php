<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM perifericos WHERE id = $id";
$result = mysqli_query($conn, $sql);
$produto = mysqli_fetch_assoc($result);

if (!$produto) {
    echo "<p>Produto não encontrado.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title><?= htmlspecialchars($produto['nome']) ?> - Detalhes</title>
    <link rel="stylesheet" href="../css/explorar.css">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="botao-link botao-voltar voltar-fixo" onclick="window.location.href='./perifericos.php'">← Voltar</div>
    <div class="bg">
        <div class="overlay"></div>
        <br><br><br>
        <div class="content">
            <nav class="breadcrumb">
            </nav>
            <br><br><br><br><br>
            <div class="pagina-produto">
                <div class="produto-detalhe">
                    <div class="imagem-grande">
                        <img src="../imagens/<?= htmlspecialchars($produto['caminho_arquivo']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                    </div>
                    <div class="info-produto">
                        <h1><?= htmlspecialchars($produto['nome']) ?></h1>
                        <p class="descricao"><?= !empty($produto['descricao']) ? nl2br(htmlspecialchars($produto['descricao'])) : 'Sem descrição disponível.' ?></p>
<?php
$precoOriginal = $produto["preco"];
$desconto = $produto["desconto"];
$inicio = $produto["tempoinicio_desconto"];
$fim = $produto["tempofim_desconto"];

$agora = time();
$inicioTime = $inicio ? strtotime($inicio) : null;
$fimTime = $fim ? strtotime($fim) : null;

if ($desconto !== null && $desconto > 0 && $inicioTime && $fimTime && $agora >= $inicioTime && $agora <= $fimTime) {
    $precoComDesconto = $precoOriginal - ($precoOriginal * ($desconto / 100));
?>
<p class="preco">
        <span style="color:red; text-decoration:line-through;">
            €<?= number_format($precoOriginal, 2) ?>
        </span>
        <br>
        <strong style="color:green; font-size:1.5rem;">
            €<?= number_format($precoComDesconto, 2) ?>
        </strong>
    </p>
    <div id="contador-individual" data-fim="<?= $fim ?>"></div>
<?php
} else {
?>
    <p class="preco">
        <strong>€<?= number_format($precoOriginal, 2) ?></strong>
    </p>
<?php
}
?>
<p class="stock">Stock disponível: <b><?= (int)$produto['stock'] ?></b></p>

<form action="../carrinho/adicionar_ao_carrinho.php" method="post" class="form-carrinho">
    
    <input type="hidden" name="id_produto" value="<?= (int)$produto['id'] ?>">
    
    
    <input type="hidden" name="tipo_produto" value="periferico"> 

    <label for="quantidade_<?= (int)$produto['id'] ?>">Quantidade:</label>
    <center>
    <input type="number"
           id="quantidade_<?= (int)$produto['id'] ?>"
           name="quantidade"
           value="1"
           min="1"
           max="<?= (int)$produto['stock'] ?>"
           required>
</center>
<br>
    <button type="submit" name="acao" value="carrinho" class="btn-adicionar">Adicionar ao carrinho</button>
    <button type="submit" name="acao" value="comprar" class="btn-adicionar">Comprar já</button>

</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($_GET['sucesso'])): ?>
<div id="toast" class="toast">Produto adicionado ao carrinho!</div>
<script>
  const toast = document.getElementById("toast");
  toast.classList.add("show");
  setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);
</script>
<style>
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  background-color: #burlywood;
  color: black;
  padding: 15px 25px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  z-index: 9999;
  opacity: 0;
  transform: translateY(-20px);
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.toast.show {
  opacity: 1;
  transform: translateY(0);
}
</style>
<?php endif; ?>
<script>
const contador = document.getElementById("contador-individual");

if (contador) {
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

        contador.innerHTML = " Desconto termina em: " + dias + "d " + horas + "h " + minutos + "m";
    }

    atualizar();
    setInterval(atualizar, 60000);
}
</script>

</body>
</html>
