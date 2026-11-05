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
<style>
    .acoes-admin {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin: 60px 0;
}

.botao-admin {
    width: 260px;
    padding: 30px 20px;
    background: #9d8b55;
    color: white;
    border: 3px solid black;
    border-radius: 12px;
    box-shadow: 4px 4px 0 black;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
}

.botao-admin:hover {
    background: #8e773f;
    color: #111;
}
</style>
<body>

    <header>
        <img src="../img/logo.png" alt="">
        <nav>
            <a href="../produtos/listagem.php">Produtos</a>
            <a href="usuarios.php">Usuários</a>
            <a href="../usuarios/logout.php">Sair</a>
        </nav>

    </header>

    <main>
        <h2>Área do Administrador</h2>
        <p style="text-align: center;">Seja Bem-Vindo(a) a sua área de administração! <br> Aqui você pode administrar os usuários e os produtos cadastrados em seu site.</p>

        <div class="acoes-admin">
            <a href="cadastro_adm.php"><button class="botao-admin">Cadastrar Usuário</button></a>
            <a href="../produtos/cadastro_produtos.php"><button class="botao-admin">Cadastrar HQ</button></a>
        </div>
    </main>

    <footer>
    </footer>

</body>

</html>