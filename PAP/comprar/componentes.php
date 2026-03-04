
<?php



include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Loja Componentes</title>
  <link rel="stylesheet" href="../css/comprar.css">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Menbere:wght@500&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@200&display=swap" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <a href="../comprar/loja.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
  <div class="overlay"></div>
  <div class="loja-container">
    <aside class="sidebar">
      <div class="search-box"></div>
    <div class="search-box">
  <input type="text" id="searchInput" placeholder="Pesquisar componentes...">
</div>
      <ul>
        <li><a href="componentes_lista.php">Todos os Tipos</a></li>
        <li class="has-sub">
          <a href="#">Placas Gráficas</a>
          <ul class="sub-menu">
            <li><a href="componentes_lista.php?tipo=placa grafica">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=placa grafica&q=GTX">NVIDIA GTX</a></li>
            <li><a href="componentes_lista.php?tipo=placa grafica&q=RTX">NVIDIA RTX</a></li>
          </ul>
        </li>
        <li class="has-sub">
          <a href="#">Processadores</a>
          <ul class="sub-menu">
            <li><a href="componentes_lista.php?tipo=processador">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=processador&q=i3">Intel Core i3</a></li>
            <li><a href="componentes_lista.php?tipo=processador&q=i5">Intel Core i5</a></li>
            <li><a href="componentes_lista.php?tipo=processador&q=i7">Intel Core i7</a></li>
            <li><a href="componentes_lista.php?tipo=processador&q=i9">Intel Core i9</a></li>
            <li><a href="componentes_lista.php?tipo=processador&q=Ryzen 3">Ryzen 3</a></li>
            <li><a href="componentes_lista.php?tipo=processador&q=Ryzen 5">Ryzen 5</a></li>
            <li><a href="componentes_lista.php?tipo=processador&q=Ryzen 7">Ryzen 7</a></li>
            <li><a href="componentes_lista.php?tipo=processador&q=Ryzen 9">Ryzen 9</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Motherboards</a>
          <ul class="sub-menu">
            <li><a href="componentes_lista.php?tipo=motherboard">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=motherboard&q=AMD">AMD</a></li>
            <li><a href="componentes_lista.php?tipo=motherboard&q=Intel">Intel</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Memória RAM</a>
          <ul class="sub-menu"> 
            <li><a href="componentes_lista.php?tipo=memoria ram">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=memoria ram&q=Corsair">Corsair</a></li>
            <li><a href="componentes_lista.php?tipo=memoria ram&q=G.SKILL">G.SKILL</a></li>
            <li><a href="componentes_lista.php?tipo=memoria ram&q=Kingston">Kingston</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Armazenamento (SSD/HDD)</a>
          <ul class="sub-menu">
            <li><a href="componentes_lista.php?tipo=armazenamento">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=armazenamento&q=Corsair">Corsair</a></li>
            <li><a href="componentes_lista.php?tipo=armazenamento&q=Kingston">Kingston</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Fontes de Alimentação</a>
          <ul class="sub-menu">
            <li><a href="componentes_lista.php?tipo=fonte de alimentaçao">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=fonte de alimentaçao&q=Asus">Asus</a></li>
            <li><a href="componentes_lista.php?tipo=fonte de alimentaçao&q=Corsair">Corsair</a></li>
            <li><a href="componentes_lista.php?tipo=fonte de alimentaçao&q=MSI">MSI</a></li>
            <li><a href="componentes_lista.php?tipo=fonte de alimentaçao&q=SeaSonic">SeaSonic</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Coolers de CPU</a>
          <ul class="sub-menu">
            <li><a href="componentes_lista.php?tipo=cooler de cpu">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=Asus">Asus</a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=Arctic">Arctic</a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=Corsair">Corsair</a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=Fractal ">Fractal Design</a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=Lian Li">Lian Li</a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=MSI">MSI</a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=Noctua">Noctua </a></li>
            <li><a href="componentes_lista.php?tipo=cooler de cpu&q=Thermalright">Thermalright </a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Ventoinhas</a>
          <ul class="sub-menu">
            <li><a href="componentes_lista.php?tipo=ventoinha">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=ventoinha&q=Arctic">Arctic</a></li>
            <li><a href="componentes_lista.php?tipo=ventoinha&q=Corsair">Corsair</a></li>
            <li><a href="componentes_lista.php?tipo=ventoinha&q=Fractal Design">Fractal Design</a></li>
            <li><a href="componentes_lista.php?tipo=ventoinha&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="componentes_lista.php?tipo=ventoinha&q=Noctua">Noctua</a></li>
            <li><a href="componentes_lista.php?tipo=ventoinha&q=Thermalright">Thermalright</a></li>
          </ul>
        </li>
        <li class="has-sub"><a href="#">Caixas / Torres</a>
          <ul class="sub-menu">
            <li><a href="componentes_lista.php?tipo=gabinete">Todos</a></li>
            <li><a href="componentes_lista.php?tipo=gabinete&q=Asus">Asus</a></li>
            <li><a href="componentes_lista.php?tipo=gabinete&q=Corsair">Corsair</a></li>
            <li><a href="componentes_lista.php?tipo=gabinete&q=Fractal">Fractal Design</a></li>
            <li><a href="componentes_lista.php?tipo=gabinete&q=Gigabyte">Gigabyte</a></li>
            <li><a href="componentes_lista.php?tipo=gabinete&q=Lian Li">Lian Li</a></li>
            <li><a href="componentes_lista.php?tipo=gabinete&q=Mars Gaming">Mars Gaming</a></li>
            <li><a href="componentes_lista.php?tipo=gabinete&q=MSI">MSI</a></li>
          </ul>
        </li>
      </ul>
      <br>

</aside>
    <main class="content" id="content">
      <h3 class="nao-encontrado">A Carregar produtos...</h3>
    </main>
  </div>
</div><script>
const input = document.getElementById('searchInput');
const content = document.getElementById('content');

function pesquisar() {
  const query = input.value.trim();
  let url = 'componentes_lista.php';
  if (query) {
    url += '?q=' + encodeURIComponent(query);
  }

  fetch(url)
    .then(response => {
      if (!response.ok) throw new Error('Erro ao pesquisar');
      return response.text();
    })
    .then(html => {
      content.innerHTML = html;
    })
    .catch(err => {
      console.error(err);
      content.innerHTML = '<p>Erro ao carregar produtos.</p>';
    });
}

input.addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    pesquisar();
  }
});

input.addEventListener('input', () => {
  pesquisar();
});

document.querySelectorAll('.sidebar a').forEach(link => {
  link.addEventListener('click', function(e) {
    const parentLi = this.closest('.has-sub');

    if (parentLi && this === parentLi.querySelector(':scope > a') && this.nextElementSibling) {
      e.preventDefault();
      parentLi.classList.toggle('open');
      return;
    }

    if (this.getAttribute('href').includes('componentes_lista.php')) {
      e.preventDefault();
      fetch(this.getAttribute('href'))
        .then(r => r.text())
        .then(html => {
          content.innerHTML = html;
        })
        .catch(err => {
          console.error(err);
          content.innerHTML = '<p>Erro ao carregar produtos.</p>';
        });
    }
  });
});

window.addEventListener('DOMContentLoaded', () => {
  fetch('componentes_lista.php')
    .then(r => r.text())
    .then(html => {
      content.innerHTML = html;
    })
    .catch(err => {
      console.error(err);
      content.innerHTML = '<p>Erro ao carregar produtos.</p>';
    });
});

document.addEventListener('click', function(e) {

  if (e.target.classList.contains('coracao')) {

    const idItem = e.target.dataset.id;
    const tipoItem = e.target.dataset.tipo;

    fetch('../favoritos/toogle_favoritos.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'id_item=' + idItem + '&tipo_item=' + tipoItem
    })
    .then(response => response.json())
    .then(data => {

      console.log(data); // 👈 muito importante para debug

      if (data.status === 'adicionado') {
        e.target.classList.add('ativo');
      }

      if (data.status === 'removido') {
        e.target.classList.remove('ativo');
      }

      if (data.status === 'erro') {
        alert("Erro ao adicionar favorito.");
      }

    })
    .catch(err => {
      console.error("Erro fetch:", err);
    });
  }

});
</script>

</body>
</html>
