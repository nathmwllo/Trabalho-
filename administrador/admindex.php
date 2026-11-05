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
// ===== Texto de introdução =====
$arquivo = __DIR__ . "/../texto_intro.txt";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["texto_intro"])) {
    $novoTexto = trim($_POST["texto_intro"]);

    if ($novoTexto === "") {
        $erro = "O texto não pode ficar vazio.";
    } elseif (file_put_contents($arquivo, $novoTexto) !== false) {
        header("Location: admindex.php?salvo=1");
        exit;
    } else {
        $erro = "Não foi possível salvar o texto.";
    }
}

if ($erro !== "") {
    $textoAtual = $_POST["texto_intro"];
} elseif (file_exists($arquivo)) {
    $textoAtual = file_get_contents($arquivo);
} else {
    $textoAtual = "Entre em um universo repleto de aventuras, ação e personagens extraordinários! Aqui na HQ Mania, você pode explorar o mundo dos quadrinhos, conhecer grandes heróis e descobrir histórias incríveis.
    Seja você fã dos heróis clássicos ou dos personagens mais recentes, temos um universo de possibilidades esperando por você.
    Prepare-se para muita emoção, poderes e aventuras. Na HQ Mania, toda história tem um herói!
    Explore o catálogo, pesquise por título ou editora e monte a sua coleção. Boas leituras!";
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
.botao-texto{
    color: #5a2929;
    border-radius: 3px;
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
        <section class="card-intro card-edicao">
            <h2>Texto de introdução</h2>

            <?php if (isset($_GET["salvo"])): ?>
                <p class="mensagem">Texto atualizado com sucesso!</p>
            <?php endif; ?>

            <?php if ($erro !== ""): ?>
                <p class="mensagem"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form method="post">
                <textarea name="texto_intro"><?= htmlspecialchars($textoAtual) ?></textarea>
                <p></p>
                <button type="submit" class="botao-texto">Salvar texto</button>
            </form>
        </section>
    </main>

    <footer>
    </footer>

</body>

</html>