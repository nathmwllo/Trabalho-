<?php
session_start();

require_once __DIR__ . "/conexao.php";
$lancamentos = $conexao->query("SELECT * FROM produtos ORDER BY id_produto DESC LIMIT 6");

$arquivo = __DIR__ . "/texto_intro.txt";
$textoIntro = file_exists($arquivo)
    ? file_get_contents($arquivo)
    : "Entre em um universo repleto de aventuras, ação e personagens extraordinários! Aqui na HQ Mania, você pode explorar o mundo dos quadrinhos, conhecer grandes heróis e descobrir histórias incríveis.
    Seja você fã dos heróis clássicos ou dos personagens mais recentes, temos um universo de possibilidades esperando por você.
    Prepare-se para muita emoção, poderes e aventuras. Na HQ Mania, toda história tem um herói!
    Explore o catálogo, pesquise por título ou editora e monte a sua coleção. Boas leituras!";
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>HQ Mania</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
    .perfil-menu {
    position: relative;
    display: inline-block;
}

.dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 180px;
    margin-top: 8px;
    padding: 6px 0;
    background: #e9e1d3;          
    border: 3px solid #000;       
    border-radius: 10px;
    box-shadow: 4px 4px 0 #000;  
    z-index: 100;
}
.dropdown.aberto {
    display: block;
}
nav .dropdown a {
    display: flex;
    flex-direction: row;        
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    width: auto;
    height: auto;
    margin: 0;
    padding: 10px 16px;
    background: transparent;
    border: none;
    border-radius: 0;
    box-shadow: none;
    color: #000;
    font-size: 14px;
    text-align: left;
    text-decoration: none;
}
nav .dropdown a img {
    width: 18px !important;
    height: 18px !important;
    margin: 0 !important;
    flex-shrink: 0;
}
nav .dropdown a:hover {
    background: #a89c5f;         
}
nav .dropdown a + a {
    border-top: 1px solid #00000030;
}

</style>
<body>

    <header>
        <img src="img/logo.png" alt="logo">
        <nav>
            <a href="usuarios/login.php" <?php if (isset($_SESSION['id_usuario'])) echo 'style="display:none"'; ?>>
                <img src="img/login.png" alt="">Login</a>
            <a href="index.php">
                <img src="img/inicio.png" alt="inicio"><span>Início</span></a>
            <a href="produtos/catalogo.php">
                <img src="img/catalogo.png" alt="catálogo">Catálogo</a>
            <a href="#">
                <img src="img/carrinho.png" alt="">Carrinho</a>
            <div class="perfil-menu">
                   <a href="#" id="btn-perfil"><img src="img/perfil.png" alt="perfil">Perfil</a>

            <div class="dropdown" id="dropdown-perfil">
                <?php if (isset($_SESSION['id_usuario'])): ?>
                 <a href="usuarios/perfil.php"> <img src="img/perfil.png" alt="perfil">Minha conta</a>
                 <a href="usuarios/logout.php"><img src="img/logout.png" alt="sair">Logout</a>
            <?php else: ?>
                 <a href="usuarios/login.php"><img src="img/login.png" alt="login">Fazer login</a>
            <?php endif; ?>
             </div>
            </div>
        </nav>
       
    </header>
         <div class="carrossel">
            <div class="slides">
                <img src="img/banner_marvel.png" alt="Marvel">
                <img src="img/banner_dc.png" alt="DC Comics">
            </div>
        </div>
    
        <main>
    <div class="home-layout">

        <section class="card-intro">
            <h2>Bem-vindo à HQ Mania!</h2>
            <?php foreach (preg_split('/\R{2,}/', $textoIntro) as $paragrafo): ?>
                <p><?= nl2br(htmlspecialchars($paragrafo)) ?></p>
            <?php endforeach; ?>

            <a href="produtos/catalogo.php" class="botao-intro">Ver catálogo</a>

            <div class="contato-intro">
                <div class="contato-item">
                    <img src="img/logo_insta.png" alt="Instagram">
                    <span>@hqmania.oficial</span>
                </div>
                <div class="contato-item">
                    <img src="img/telefone.png" alt="Telefone">
                    <span>(51) 99999-0000</span>
                </div>
            </div>
        </section>

        <section class="lancamentos">
            <h2>Lançamentos</h2>
            <div class="catalogo">
                <?php while ($produto = $lancamentos->fetch_assoc()): ?>
                    <div>
                        <?php if (!empty($produto["imagem"])): ?>
                            <img src="produtos/<?= htmlspecialchars($produto['imagem']) ?>" alt="Capa">
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($produto["nome_produto"]) ?></h3>
                        <p>Edição: <?= htmlspecialchars($produto["edicao"]) ?></p>
                        <p>Preço: R$ <?= number_format($produto["preco"], 2, ",", ".") ?></p>
                        <p><?= $produto["estoque"] > 0 ? "Disponível em estoque" : "Indisponível em estoque" ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
</main>

    <footer>
    </footer>
<script>
    const btnPerfil = document.getElementById('btn-perfil');
    const dropdown = document.getElementById('dropdown-perfil');

    btnPerfil.addEventListener('click', function (e) {
        e.preventDefault();          
        dropdown.classList.toggle('aberto');
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.perfil-menu')) {
            dropdown.classList.remove('aberto');
        }
    });
</script>

</body>

</html>