  <?php
  session_start();
  include_once __DIR__ . '/../db.php';
  include_once __DIR__ . '/../cabecindex.php';
  ?>

  <!DOCTYPE html>
  <html lang="pt">
  <head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title>Loja Periféricos</title>
    <link rel="stylesheet" href="../css/comprar.css">
  <link rel="icon" type="image/png" href="/imagens/logo.png?v=2">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
        <a href="../comprar/loja.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
  <div class="bg">
    <div class="loja-container">
    <div class="overlay"></div>
      <aside class="sidebar">
        <div class="search-box"></div>
      <div class="search-box">
    <input type="text" id="searchInput" placeholder="Pesquisar periféricos...">
  </div>
        <ul>
          <li><a href="perifericos_lista.php">Todos os Tipos</a></li>
          <li class="has-sub"><a href="#">Fones</a>
            <ul class="sub-menu">
              <li><a href="?tipo=Fones">Todos</a></li>
              <li><a href="?tipo=Fones&q=Asus">Asus</a></li>
              <li><a href="?tipo=Fones&q=Corsair">Corsair</a></li>
              <li><a href="?tipo=Fones&q=HyperX">HyperX</a></li>
              <li><a href="?tipo=Fones&q=Logitech">Logitech</a></li>
              <li><a href="?tipo=Fones&q=Mars Gaming">Mars Gaming</a></li>
              <li><a href="?tipo=Fones&q=Razer">Razer</a></li>

            </ul>
          </li>
          <li class="has-sub"><a href="#">Teclados</a>
            <ul class="sub-menu">
              <li><a href="?tipo=Teclado">Todos</a></li>
              <li><a href="?tipo=Teclado&q=Asus">Asus</a></li>
              <li><a href="?tipo=Teclado&q=Corsair">Corsair</a></li>
              <li><a href="?tipo=Teclado&q=Gigabyte">Gigabyte</a></li>
              <li><a href="?tipo=Teclado&q=HyperX">HyperX</a></li>
              <li><a href="?tipo=Teclado&q=Logitech">Logitech</a></li>
              <li><a href="?tipo=Teclado&q=Mars Gaming">Mars Gaming</a></li>
              <li><a href="?tipo=Teclado&q=Razer">Razer</a></li>
              <li><a href="?tipo=Teclado&q=SteelSeries">SteelSeries</a></li>
            </ul>
          </li>
          <li class="has-sub"><a href="#">Ratos</a>
            <ul class="sub-menu">
              <li><a href="?tipo=Rato">Todos</a></li>
              <li><a href="?tipo=Rato&q=Asus">Asus</a></li>
              <li><a href="?tipo=Rato&q=Corsair">Corsair</a></li>
              <li><a href="?tipo=Rato&q=Gigabyte">Gigabyte</a></li>
              <li><a href="?tipo=Rato&q=HyperX">HyperX</a></li>
              <li><a href="?tipo=Rato&q=Logitech">Logitech</a></li>
              <li><a href="?tipo=Rato&q=Mars Gaming">Mars Gaming</a></li>
              <li><a href="?tipo=Rato&q=NPlay">NPlay</a></li>
              <li><a href="?tipo=Rato&q=Razer">Razer</a></li>
              
            </ul>
          </li>
          <li class="has-sub"><a href="#">Tapetes de Rato</a>
            <ul class="sub-menu">
              <li><a href="?tipo=Tapete">Todos</a></li>
              <li><a href="?tipo=Tapete&q=Asus">Asus</a></li>
              <li><a href="?tipo=Tapete&q=Corsair">Corsair</a></li>
              <li><a href="?tipo=Tapete&q=Logitech">Logitech</a></li>
              <li><a href="?tipo=Tapete&q=Mars Gaming">Mars Gaming</a></li>
              <li><a href="?tipo=Tapete&q=Razer">Razer</a></li>
            </ul>
          </li>
          <li class="has-sub"><a href="#">Monitores</a>
            <ul class="sub-menu">
              <li><a href="?tipo=Monitor">Todos</a></li>
              <li><a href="?tipo=Monitor&q=Asus">Asus</a></li>
              <li><a href="?tipo=Monitor&q=AOC">AOC</a></li>
              <li><a href="?tipo=Monitor&q=Acer">Acer</a></li>
              <li><a href="?tipo=Monitor&q=BenQ">BenQ</a></li>
              <li><a href="?tipo=Monitor&q=Gigabyte">Gigabyte</a></li>
              <li><a href="?tipo=Monitor&q=HP">HP</a></li>
              <li><a href="?tipo=Monitor&q=LG">LG</a></li>
              <li><a href="?tipo=Monitor&q=MSI">MSI</a></li>
            </ul>
          </li>
        </ul>
        <br>
    <div class="caixa-container">
 
      </aside>

      <main class="content" id="content">
        <h3 class="nao-encontrado">A Carregar produtos...</h3>
      </main>
    </div>
  </div>
  <script>
  const input = document.getElementById('searchInput');
  const content = document.getElementById('content');

  function pesquisar() {
    const query = input.value.trim();
    let url = 'perifericos_lista.php';
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

      if (this.getAttribute('href').includes('perifericos_lista.php')) {
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
    fetch('perifericos_lista.php')
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
  </script>

  </body>
  </html>
