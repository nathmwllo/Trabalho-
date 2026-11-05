
<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["id_usuario"];

$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();

$pesquisa = trim($_GET["pesquisa"] ?? "");

$sql = "SELECT * FROM pedido WHERE id_usuario = ?";

if ($pesquisa != "") {

    if (is_numeric($pesquisa)) {
        $sql .= " AND id_pedido = ?";
        $stmt = $conexao->prepare($sql);
        $numero = (int) $pesquisa;
        $stmt->bind_param("ii", $id_usuario, $numero);

    } else {
        $data = DateTime::createFromFormat("!d/m/Y", $pesquisa);

        if ($data && $data->format("d/m/Y") == $pesquisa) {
            $data = $data->format("Y-m-d");

            $sql .= " AND DATE(data_pedido) = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("is", $id_usuario, $data);

        } else {
            $sql .= " AND 1 = 0";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("i", $id_usuario);
        }
    }

} else {
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
}

$sql = " ORDER BY id_pedido DESC";

$stmt->execute();
$pedidos = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha conta - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">
</head>
<style>
    .sair{
    display: inline-block;
    margin-top: 20px;
    font-weight: bold;
    text-decoration: none;
    color: #111;
    }
    .sair:hover{
        color: #6b2828;
        text-decoration: underline;
    }
</style>

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
            <p><strong>Nome:</strong> <?= htmlspecialchars($usuario["nome"]) ?></p>
            <p><strong>CPF:</strong> <?= htmlspecialchars($usuario["cpf"]) ?></p>
            <p><strong>Data de nascimento:</strong>
                <?= date("d/m/Y", strtotime($usuario["data_nasc"])) ?>
            </p>
            <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario["telefone"]) ?></p>
            <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario["email"]) ?></p>
            <div class="acoes-perfil">
                <a href="editar_usuario.php" class="botao-perfil">
                    Editar dados
                </a>
                <a href="excluir_usuario.php" class="botao-excluir">
                    Excluir conta
                </a>
                <a href="logout.php" class="sair">Sair</a>
            </div>
        </section>
        <section class="caixa-compras">
            <h3>Minhas compras</h3>
            <form method="GET" action="perfil.php" class="pesquisa-compras">
                <input type="text" name="pesquisa" placeholder="Pesquisar por número do pedido ou data" value="<?= htmlspecialchars($pesquisa) ?>">
                <button type="submit">Pesquisar</button>
                <a href="perfil.php">Limpar</a>
            </form>
            <?php if ($pedidos->num_rows > 0): ?>
                <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                    <details class="pedido">
                        <summary>
                            Pedido #<?= $pedido["id_pedido"] ?>
                            <?= date("d/m/Y", strtotime($pedido["data_pedido"])) ?>
                            R$ <?= number_format($pedido["valor_pedido"], 2, ",", ".") ?>
                        </summary>
                        <p><strong>Status:</strong><?= htmlspecialchars($pedido["status_pedido"]) ?></p>
                        <h4>Produtos comprados</h4>
                        <div class="lista-itens">
                            <?php
                            $id_pedido = $pedido["id_pedido"];
                            $sqlItens = "SELECT * FROM item_pedido WHERE id_pedido = ?";
                            $stmtItens = $conexao->prepare($sqlItens);
                            $stmtItens->bind_param("i", $id_pedido);
                            $stmtItens->execute();
                            $itens = $stmtItens->get_result();
                            ?>
                            <?php while ($item = $itens->fetch_assoc()): ?>
                                <?php
                                $id_produto = $item["id_produto"];
                                $sqlProduto = "SELECT nome_produto FROM produtos WHERE id_produto = ?";
                                $stmtProduto = $conexao->prepare($sqlProduto);
                                $stmtProduto->bind_param("i", $id_produto);
                                $stmtProduto->execute();
                                $produto = $stmtProduto->get_result()->fetch_assoc();
                                $stmtProduto->close();
                                ?>
                                <div class="item-compra">
                                    <p><strong>Produto:</strong><?= htmlspecialchars($produto["nome_produto"] ?? "Produto não encontrado") ?></p>
                                    <p><strong>Quantidade:</strong><?= $item["quantidade"] ?></p>
                                    <p><strong>Preço unitário:</strong>R$ <?= number_format($item["preci_unit"], 2, ",", ".") ?></p>
                                    <p><strong>Subtotal:</strong>R$ <?= number_format($item["valor_pedido"], 2, ",", ".") ?></p>
                                </div>
                            <?php endwhile; ?>
                            <?php $stmtItens->close(); ?>
                        </div>
                    </details>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhuma compra encontrada.</p>
            <?php endif; ?>
        </section>
    </div>

    <?php $stmt->close(); ?>
</main>

<footer></footer>

</body>
</html>
