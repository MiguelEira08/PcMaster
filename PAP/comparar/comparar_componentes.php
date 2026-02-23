<?php
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Comparar Componentes</title>
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
  <div class="loja-container">
    <div class="sidebar">
      <div class="search-box"></div>
    <div class="search-box">
  <input type="text" id="searchInput" placeholder="Pesquisar componentes...">
</div>
    <ul>
        <li><a href="comparar_componentes_lista.php">Todos os Tipos</a></li>
        <li class="has-sub">
          <a href="#">Placas Gráficas</a>
          <ul class="sub-menu">
            <li><a href="comparar_componentes_lista.php?tipo=placa grafica">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=placa grafica&q=GTX">NVIDIA GTX</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=placa grafica&q=RTX">NVIDIA RTX</a></li>
          </ul>
        </li>
        <li class="has-sub">
          <a href="#">Processadores</a>
          <ul class="sub-menu">
            <li><a href="comparar_componentes_lista.php?tipo=processador">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=processador&q=i3">Intel Core i3</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=processador&q=i5">Intel Core i5</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=processador&q=i7">Intel Core i7</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=processador&q=i9">Intel Core i9</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=processador&q=Ryzen 3">Ryzen 3</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=processador&q=Ryzen 5">Ryzen 5</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=processador&q=Ryzen 7">Ryzen 7</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=processador&q=Ryzen 9">Ryzen 9</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Motherboards</a>
          <ul class="sub-menu">
            <li><a href="comparar_componentes_lista.php?tipo=motherboard">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=motherboard&q=AMD">AMD</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=motherboard&q=Intel">Intel</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Memória RAM</a>
          <ul class="sub-menu"> 
            <li><a href="comparar_componentes_lista.php?tipo=memoria ram">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=memoria ram&q=Corsair">Corsair</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=memoria ram&q=G.SKILL">G.SKILL</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=memoria ram&q=Kingston">Kingston</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Armazenamento (SSD/HDD)</a>
          <ul class="sub-menu">
            <li><a href="comparar_componentes_lista.php?tipo=armazenamento">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=armazenamento&q=Corsair">Corsair</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=armazenamento&q=Kingston">Kingston</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Fontes de Alimentação</a>
          <ul class="sub-menu">
            <li><a href="comparar_componentes_lista.php?tipo=fonte de alimentaçao">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=fonte de alimentaçao&q=Asus">Asus</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=fonte de alimentaçao&q=Corsair">Corsair</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=fonte de alimentaçao&q=MSI">MSI</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=fonte de alimentaçao&q=SeaSonic">SeaSonic</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Coolers de CPU</a>
          <ul class="sub-menu">
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=Asus">Asus</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=Arctic">Arctic</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=Corsair">Corsair</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=Fractal ">Fractal Design</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=Lian Li">Lian Li</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=MSI">MSI</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=Noctua">Noctua </a></li>
            <li><a href="comparar_componentes_lista.php?tipo=cooler de cpu&q=Thermalright">Thermalright </a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Ventoinhas</a>
          <ul class="sub-menu">
            <li><a href="comparar_componentes_lista.php?tipo=ventoinha">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=ventoinha&q=Arctic">Arctic</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=ventoinha&q=Corsair">Corsair</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=ventoinha&q=Fractal Design">Fractal Design</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=ventoinha&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=ventoinha&q=Noctua">Noctua</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=ventoinha&q=Thermalright">Thermalright</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Caixas / Torres</a>
          <ul class="sub-menu">
            <li><a href="comparar_componentes_lista.php?tipo=gabinete">Todos</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=gabinete&q=Asus">Asus</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=gabinete&q=Corsair">Corsair</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=gabinete&q=Fractal">Fractal Design</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=gabinete&q=Gigabyte">Gigabyte</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=gabinete&q=Lian Li">Lian Li</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=gabinete&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="comparar_componentes_lista.php?tipo=gabinete&q=MSI">MSI</a></li>
          </ul>
        </li>
      </ul>
<br>
      <div class="caixa-container">

      </div>
    </div>

    <main class="content" id="content">
<div id="toast" class="toast">
  Produto adicionado à comparação!
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
    </main>
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

    if (this.getAttribute('href').includes('comparar_componentes_lista.php')) {
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
  fetch('comparar_componentes_lista.php')
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
    let url = 'comparar_componentes_lista.php';
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

