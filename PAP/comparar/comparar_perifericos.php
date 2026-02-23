<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Loja Componentes</title>
    <link rel="stylesheet" href="../css/comparar.css">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
     <a href="../comparar/comparar.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>  
    <div class="content">
    <div class="sidebar">
      <div class="search-box"></div>
    <div class="search-box">
  <input type="text" id="searchInput" placeholder="Pesquisar periféricos...">
</div>
               <ul>
        <li><a href="comparar_perifericos_lista.php">Todos os Tipos</a></li>
        <li class="has-sub"><a href="#">Fones</a>
          <ul class="sub-menu">
            <li><a href="comparar_perifericos_lista.php?tipo=Fones">Todos</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Fones&q=Asus">Asus</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Fones&q=Corsair">Corsair</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Fones&q=HyperX">HyperX</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Fones&q=Logitech">Logitech</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Fones&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Fones&q=Razer">Razer</a></li>

          </ul>
        </li>
        <li class="has-sub"><a href="#">Teclados</a>
          <ul class="sub-menu">
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado">Todos</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado&q=Asus">Asus</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado&q=Corsair">Corsair</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado&q=Gigabyte">Gigabyte</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado&q=HyperX">HyperX</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado&q=Logitech">Logitech</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado&q=Razer">Razer</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Teclado&q=SteelSeries">SteelSeries</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Ratos</a>
          <ul class="sub-menu">
            <li><a href="comparar_perifericos_lista.php?tipo=Rato">Todos</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Rato&q=Asus">Asus</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Rato&q=Corsair">Corsair</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Rato&q=Gigabyte">Gigabyte</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Rato&q=HyperX">HyperX</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Rato&q=Logitech">Logitech</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Rato&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Rato&q=NPlay">NPlay</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Rato&q=Razer">Razer</a></li>
            
          </ul>
        </li>
        <li class="has-sub"><a href="#">Tapetes de Rato</a>
          <ul class="sub-menu">
            <li><a href="comparar_perifericos_lista.php?tipo=Tapete">Todos</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Tapete&q=Asus">Asus</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Tapete&q=Corsair">Corsair</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Tapete&q=Logitech">Logitech</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Tapete&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Tapete&q=Razer">Razer</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Monitores</a>
          <ul class="sub-menu">
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor">Todos</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor&q=Asus">Asus</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor&q=AOC">AOC</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor&q=Acer">Acer</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor&q=BenQ">BenQ</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor&q=Gigabyte">Gigabyte</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor&q=HP">HP</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor&q=LG">LG</a></li>
            <li><a href="comparar_perifericos_lista.php?tipo=Monitor&q=MSI">MSI</a></li>
          </ul>
        </li>
      </ul>
<br>
</div>


<div class="content">
  <div id="toast" class="toast">

</div>
        <div class="comparacao-box" id="caixa-comparacao">
            <h2>Comparação de Produtos</h2>
            <div id="produto1">Produto 1: Nenhum</div>
            <div id="produto2">Produto 2: Nenhum</div>
            <div class="alinhar"><button class="botao-link" onclick="comparar()">Comparar</button></div>
            <div id="resultado-comparacao"></div>
        </div>
        
<div id="lista-produtos">
   <h3 class="nao-encontrado">A Carregar produtos...</h3>
   </div>
   </div>
</div>

<script>
let produto1 = null;
let produto2 = null;

function atualizarCaixa() {
  document.getElementById("produto1").innerText = produto1 ? "Produto 1: " + produto1.nome : "Produto 1: Nenhum";
  document.getElementById("produto2").innerText = produto2 ? "Produto 2: " + produto2.nome : "Produto 2: Nenhum";
}

function adicionarComparacao(produto) {
  if (!produto1) {
    produto1 = produto;
    mostrarToast("Produto adicionado!");
  } 
  else if (!produto2 && produto.id !== produto1.id) {
    produto2 = produto;
    mostrarToast("Produto adicionado!");
  } 
  else if (produto.id === produto1?.id || produto.id === produto2?.id) {
    mostrarToast("Este produto já foi selecionado.");
    return;
  } 
  else {
    mostrarToast("Só pode comparar dois produtos de cada vez!");
    return;
  }

  atualizarCaixa();
}

function comparar() {
  if (!produto1 || !produto2) {
    alert("Selecione dois produtos para comparar.");
    return;
  }

  const div = document.getElementById("resultado-comparacao");
  div.innerHTML = `
    <div class="descricao-produto">
      <strong>${produto1.nome}</strong><br>
      ${produto1.descricao}
    </div>
    <div class="descricao-produto">
      <strong>${produto2.nome}</strong><br>
      ${produto2.descricao}
    </div>
  `;
}

document.querySelectorAll('.sidebar a').forEach(link => {
  link.addEventListener('click', function(e) {
    const parentLi = this.closest('.has-sub');

    if (parentLi && this === parentLi.querySelector(':scope > a') && this.nextElementSibling) {
      e.preventDefault();
      parentLi.classList.toggle('open');
      return;
    }

    if (this.getAttribute('href').includes('comparar_perifericos_lista.php')) {
      e.preventDefault();
      fetch(this.getAttribute('href'))
        .then(response => {
          if (!response.ok) throw new Error('Erro ao carregar os dados');
          return response.text();
        })
        .then(html => {
          document.getElementById('lista-produtos').innerHTML = html;
        })
        .catch(err => {
          console.error(err);
          alert('Não foi possível carregar os componentes.');
        });
    }
  });
});

window.addEventListener('DOMContentLoaded', () => {
  fetch('comparar_perifericos_lista.php')
    .then(response => response.text())
    .then(html => {
      document.getElementById('lista-produtos').innerHTML = html;
    })
    .catch(err => {
      console.error(err);
      document.getElementById('lista-produtos').innerHTML = '<p>Não foi possível carregar os produtos.</p>';
    });
});

const searchInput = document.getElementById('searchInput');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    const query = searchInput.value.trim();
    let url = 'comparar_perifericos_lista.php';
    if (query) {
      url += '?q=' + encodeURIComponent(query);
    }
    fetch(url)
      .then(r => r.text())
      .then(html => {
        document.getElementById('lista-produtos').innerHTML = html;
      })
      .catch(err => {
        console.error(err);
        document.getElementById('lista-produtos').innerHTML = '<p>Erro ao pesquisar produtos.</p>';
      });
  });
}
  function mostrarToast(mensagem) {
  const toast = document.getElementById("toast");
  if (!toast) return;

  toast.innerText = mensagem;
  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);
  }
</script>

</body>
</html>
