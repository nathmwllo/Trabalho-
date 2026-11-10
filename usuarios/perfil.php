<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["id_usuario"];

$sql = "SELECT * FROM usuarios WHERE id_usuario = $id_usuario";
$resultado = $conexao->query($sql);
$usuario = $resultado->fetch_assoc();

$pesquisa = $_GET["pesquisa"] ?? "";

$sql = "SELECT * FROM pedido WHERE id_usuario = $id_usuario ORDER BY id_pedido DESC";
$resultado = $conexao->query($sql);

$pedidos = [];

while ($pedido = $resultado->fetch_assoc()) {

    $encontrou = false;

    if ($pesquisa == "" ||
        stripos($pedido["id_pedido"], $pesquisa) !== false ||
        stripos($pedido["data_pedido"], $pesquisa) !== false ||
        stripos($pedido["status_pedido"], $pesquisa) !== false ||
        stripos($pedido["valor_pedido"], $pesquisa) !== false) {

        $encontrou = true;
    }

    $id_pedido = $pedido["id_pedido"];

    $sqlItens = "SELECT * FROM item_pedido WHERE id_pedido = $id_pedido";
    $resultadoItens = $conexao->query($sqlItens);

    $itens = [];

    while ($item = $resultadoItens->fetch_assoc()) {

        $id_produto = $item["id_produto"];

        $sqlProduto = "SELECT * FROM produtos WHERE id_produto = $id_produto";
        $resultadoProduto = $conexao->query($sqlProduto);

        $produto = $resultadoProduto->fetch_assoc();

        $item["nome_produto"] = $produto["nome_produto"] ?? "Produto não encontrado";

        if ($pesquisa != "") {

            if (
                stripos($item["nome_produto"], $pesquisa) !== false ||
                stripos($item["quantidade"], $pesquisa) !== false ||
                stripos($item["preci_unit"], $pesquisa) !== false
            ) {
                $encontrou = true;
            }
        }

        $itens[] = $item;
    }

    if ($encontrou) {
        $pedido["itens"] = $itens;
        $pedidos[] = $pedido;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha conta - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<header>
    <img src="../img/logo.png" alt="Logo HQ Mania">

    <nav>
        <a href="../index.php">
            <img src="../img/inicio.png" alt="">
            <span>Início</span>
        </a>

        <a href="../produtos/catalogo.php">
            <img src="../img/catalogo.png" alt="">
            <span>Catálogo</span>
        </a>

        <a href="carrinho.php">
            <img src="../img/carrinho.png" alt="">
            <span>Carrinho</span>
        </a>

        <a href="perfil.php">
            <img src="../img/perfil.png" alt="">
            <span>Perfil</span>
        </a>
    </nav>
</header>

<main class="pagina-perfil">

    <h2>Minha conta</h2>

    <div class="colunas-perfil">

        <section class="caixa-perfil">

            <h3>Meus dados</h3>

            <?php
            echo "<p><strong>Nome:</strong> " . htmlspecialchars($usuario["nome"]) . "</p>";
            echo "<p><strong>CPF:</strong> " . htmlspecialchars($usuario["cpf"]) . "</p>";
            echo "<p><strong>Data de nascimento:</strong> " . date("d/m/Y", strtotime($usuario["data_nasc"])) . "</p>";
            echo "<p><strong>Telefone:</strong> " . htmlspecialchars($usuario["telefone"]) . "</p>";
            echo "<p><strong>E-mail:</strong> " . htmlspecialchars($usuario["email"]) . "</p>";
            ?>

            <div class="acoes-perfil">
                <a href="editar_usuario.php" class="botao-perfil">
                    Editar dados
                </a>
                <a href="excluir_usuario.php" class="botao-excluir">
                    Excluir conta
                </a>
                <a href="logout.php" class="sair">
                    Sair
                </a>

            </div>

        </section>

        <section class="caixa-compras">

            <h3>Minhas compras</h3>

            <form method="GET" action="perfil.php" class="pesquisa-compras">

<input type="text" name="pesquisa" placeholder="Pesquisar compras..." value="<?php echo htmlspecialchars($pesquisa); ?>">

                <button type="submit">Pesquisar</button>

                <a href="perfil.php" style="font-size:13px;">Limpar</a>

            </form>

            <?php if (count($pedidos) > 0): ?>

                <?php foreach ($pedidos as $pedido): ?>

                    <div class="pedido">

                        <h4>Pedido #<?php echo $pedido["id_pedido"]; ?></h4>

                        <?php
                        echo "<p><strong>Data:</strong> " . date("d/m/Y", strtotime($pedido["data_pedido"])) . "</p>";

                        echo "<p><strong>Valor:</strong> R$ " . number_format($pedido["valor_pedido"], 2, ",", ".") . "</p>";

                        echo "<p><strong>Status:</strong> " . htmlspecialchars($pedido["status_pedido"]) . "</p>";
                        ?>

                        <h4>Produtos comprados</h4>

                        <?php foreach ($pedido["itens"] as $item): ?>

                            <div class="item-compra">

                                <?php
                                echo "<p><strong>Produto:</strong> " . htmlspecialchars($item["nome_produto"]) . "</p>";

                                echo "<p><strong>Quantidade:</strong> " . htmlspecialchars($item["quantidade"]) . "</p>";

                                echo "<p><strong>Preço unitário:</strong> R$ " . number_format($item["preci_unit"], 2, ",", ".") . "</p>";

                                echo "<p><strong>Subtotal:</strong> R$ " . number_format($item["valor_pedido"], 2, ",", ".") . "</p>";
                                ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p>Nenhuma compra encontrada.</p>

            <?php endif; ?>

        </section>

    </div>

</main>

<footer></footer>

</body>
</html>
