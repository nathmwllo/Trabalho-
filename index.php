<?php
session_start();
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
            <a href="login.php" <?php if (isset($_SESSION['id_usuario'])) echo 'style="display:none"'; ?>>
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
        <h2>Bem-vindo à HQ Mania!</h2>
        <p style="text-align: center;">Seja Bem-Vindo(a) ao seu universo de quadrinhos!</p>
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