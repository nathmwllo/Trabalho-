<?php
session_start();
require_once "../conexao.php";

$pesquisa = trim($_GET["pesquisa"] ?? "");

$sql = "SELECT * FROM produtos
        WHERE nome_produto LIKE ? OR editora LIKE ?
        ORDER BY id_produto DESC";

$stmt = $conexao->prepare($sql);

$termo = "%" . $pesquisa . "%";
$stmt->bind_param("ss", $termo, $termo);
$stmt->execute();

$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - HQ Mania</title>
</head>

<body>

<header>
        <img src="img/logo.png" alt="logo">
        <nav>
            <a href="usuarios/login.php">
                <img src="img/login.png" alt="login">Login</a>
            <a href="index.php">
                <img src="img/inicio.png" alt="inicio">Início</a>
            <a href="produtos/catalogo.php">
                <img src="img/catalogo.png" alt="catálogo">Catálogo</a>
            <a href="#">
                <img src="img/carrinho.png" alt="">Carrinho</a>
            <a href="#">
                <img src="img/perfil.png" alt="perfil">Perfil</a>
        </nav>
    </header>

<h2>Catálogo de quadrinhos</h2>

<form method="GET" action="catalogo.php">
    <input
        type="text"
        name="pesquisa"
        placeholder="Pesquisar quadrinho ou editora"
        value="<?= htmlspecialchars($pesquisa) ?>"
    >
    <button type="submit">Pesquisar</button>
</form>

<hr>

<?php if ($resultado->num_rows > 0): ?>

    <?php while ($produto = $resultado->fetch_assoc()): ?>

        <div>
            <button
                type="button"
                onclick="mostrarDetalhes(<?= (int) $produto['id_produto'] ?>)"
            >
                <?php if (!empty($produto["imagem"])): ?>
                    <img
                        src="<?= htmlspecialchars($produto['imagem']) ?>"
                        alt="Capa do quadrinho"
                        width="150"
                    >
                <?php endif; ?>

                <h3><?= htmlspecialchars($produto["nome_produto"]) ?></h3>
            </button>

            <p>
                Preço: R$
                <?= number_format((float) $produto["preco"], 2, ",", ".") ?>
            </p>

            <p>
                <?php if ((int) $produto["estoque"] <= 0): ?>
                    Fora de estoque
                <?php elseif ((int) $produto["estoque"] <= 5): ?>
                    Últimas unidades! Restam <?= (int) $produto["estoque"] ?>
                <?php else: ?>
                    Em estoque
                <?php endif; ?>
            </p>

            <?php if ((int) $produto["estoque"] > 0): ?>
                <form action="adicionar_carrinho.php" method="POST">
                    <input
                        type="hidden"
                        name="id_produto"
                        value="<?= (int) $produto["id_produto"] ?>"
                    >
                    <input type="hidden" name="quantidade" value="1">
                    <button type="submit">Adicionar ao carrinho</button>
                </form>
            <?php endif; ?>

            <div
                id="detalhes-<?= (int) $produto['id_produto'] ?>"
                hidden
            >
                <h3>Informações adicionais</h3>

                <p>
                    <strong>Editora:</strong>
                    <?= htmlspecialchars($produto["editora"] ?? "Não informada") ?>
                </p>

                <p>
                    <strong>Edição:</strong>
                    <?= htmlspecialchars($produto["edicao"] ?? "Não informada") ?>
                </p>

                <p>
                    <strong>Sinopse:</strong><br>
                    <?= nl2br(htmlspecialchars($produto["sinopse"] ?? "Sinopse não cadastrada.")) ?>
                </p>

                <p>
                    <strong>Estoque:</strong>
                    <?= (int) $produto["estoque"] ?>
                </p>

                <?php if ((int) $produto["estoque"] > 0): ?>
                    <form action="adicionar_carrinho.php" method="POST">
                        <input
                            type="hidden"
                            name="id_produto"
                            value="<?= (int) $produto["id_produto"] ?>"
                        >
                        <input type="hidden" name="quantidade" value="1">
                        <button type="submit">Adicionar ao carrinho</button>
                    </form>
                <?php endif; ?>

                <button
                    type="button"
                    onclick="mostrarDetalhes(<?= (int) $produto['id_produto'] ?>)"
                >
                    Fechar informações
                </button>
            </div>
        </div>

        <hr>

    <?php endwhile; ?>

<?php else: ?>

    <p>Nenhum quadrinho encontrado.</p>

<?php endif; ?>

<script>
function mostrarDetalhes(id) {
    const detalhes = document.getElementById("detalhes-" + id);
    detalhes.hidden = !detalhes.hidden;
}
</script>

</body>
</html>

<?php
$stmt->close();
$conexao->close();
?>

