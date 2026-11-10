<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../conexao.php";
require_once "carrinho_funcoes.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$carrinho = new Carrinho($conexao, $_SESSION["id_usuario"]);

if (isset($_SESSION["produto_adicionar"])) {
    $adicionado = $carrinho->adicionar((int) $_SESSION["produto_adicionar"]);
    unset($_SESSION["produto_adicionar"]);}

if (isset($_POST["adicionar"])) {
    $carrinho->adicionar((int) $_POST["id_produto"]);

    header("Location: carrinho.php");
    exit;
}
if (isset($_POST["adicionar"])) {
    $carrinho->adicionar((int) $_POST["id_produto"]);

    header("Location: carrinho.php");
    exit;
}


if (isset($_POST["remover"])) {
    $carrinho->remover((int) $_POST["remover"]);
    header("Location: carrinho.php");
    exit;
}

if (isset($_POST["atualizar"])) {
    foreach ($_POST["quantidades"] ?? [] as $id => $quantidade) {
        if (!$carrinho->alterarQuantidade((int) $id, (int) $quantidade)) {
            $_SESSION["aviso"] = "Quantidade inválida ou maior que o estoque disponível.";
            break;
        }
    }
    header("Location: carrinho.php");
    exit;
}

$itens = $carrinho->listar();
$total = 0;

foreach ($itens as $item) {
    $total += $item["subtotal"];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">

</head>
<body>

<?php if (isset($_SESSION["aviso"])): ?>
    <script>
        alert(<?php echo json_encode($_SESSION["aviso"], JSON_UNESCAPED_UNICODE); ?>);
    </script>
    <?php unset($_SESSION["aviso"]); ?>
<?php endif; ?>

<header>
    <img src="../img/logo.png" alt="Logo HQ Mania">
    <nav>
        <a href="../index.php"><img src="../img/inicio.png" alt="">Início</a>
        <a href="../produtos/catalogo.php"><img src="../img/catalogo.png" alt="">Catálogo</a>
        <a href="carrinho.php"><img src="../img/carrinho.png" alt="">Carrinho</a>
        <a href="../usuarios/perfil.php"><img src="../img/perfil.png" alt="">Perfil</a>
    </nav>
</header>

<main class="pagina-carrinho">
    <h2>Meu carrinho</h2>

    <?php if (count($itens) > 0): ?>
        <form method="POST" action="finalizar_compra.php">
            <?php foreach ($itens as $item): ?>
                <div class="produto-carrinho">
                    <input class="selecao-produto" type="checkbox"
                           name="selecionados[]"
                           value="<?php echo (int) $item["id_produto"]; ?>"
                           checked>

                    <img src="../produtos/<?php echo htmlspecialchars($item["imagem"]); ?>"
                         alt="<?php echo htmlspecialchars($item["nome_produto"]); ?>">

                    <div class="informacoes-produto">
                        <h3><?php echo htmlspecialchars($item["nome_produto"]); ?></h3>
                        <p>Preço: R$ <?php echo number_format($item["preco"], 2, ",", "."); ?></p>
                        <p>Quantidade:
                            <input type="number"
                                   name="quantidades[<?php echo (int) $item["id_produto"]; ?>]"
                                   value="<?php echo (int) $item["quantidade"]; ?>"
                                   min="1"
                                   max="<?php echo (int) $item["estoque"]; ?>"
                                   required>
                        </p>
                        <p>Subtotal: R$ <?php echo number_format($item["subtotal"], 2, ",", "."); ?></p>
                        <button type="submit" name="remover"
                                value="<?php echo (int) $item["id_produto"]; ?>"
                                formaction="carrinho.php" formmethod="POST" formnovalidate>
                            Remover
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <p class="total-carrinho">
                Total do carrinho: R$ <?php echo number_format($total, 2, ",", "."); ?>
            </p>

            <div class="acoes-carrinho">
                <button type="submit" name="atualizar"
                        formaction="carrinho.php" formmethod="POST">
                    Atualizar quantidades
                </button>
                <button type="submit">
                    Finalizar compra
                </button>
            </div>
        </form>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>
</main>
</body>
</html>
