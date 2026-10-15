<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../usuarios/login.php");
    exit;
}

if ($_SESSION["cargo"] != "Administrador") {
    header("Location: ../index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Administração - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <header>
        <img src="../img/logo.png" alt="">
        <nav>
            <a href="../produtos/listagem.php">Produtos</a>
            <a href="../produtos/cadastro_produtos.php">Cadastrar HQ</a>
            <a href="usuarios.php">Usuários</a>
            <a href="../usuarios/logout.php">Sair</a>
        </nav>

    </header>


    <!-- CONTEÚDO -->

    <main>
        <h2>Área do Administrador</h2>
        <p style="text-align: center;">Seja Bem-Vindo(a) a sua área de administração! <br> Aqui você pode administrar os usuários e os produtos cadastrados em seu site.</p>
    </main>

    <footer>
    </footer>

</body>

</html>