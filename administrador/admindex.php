<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
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
        <h1>HQ MANIA</h1>
        <nav>
            <a href="cadastro_adm.php">Cadastro de usuário</a>
            <a href="../usuarios/catalogo.php">Catálogo</a>
            <a href="produtos_adm.php">Cadastro de produto</a>
            <a href="usuarios.php">Usuários</a>
            <a href="../logout.php">Sair</a>
        </nav>

    </header>


    <!-- CONTEÚDO -->

    <main>
        <h2>Área do Administrador</h2>
    </main>

    <footer>
    </footer>

</body>

</html>