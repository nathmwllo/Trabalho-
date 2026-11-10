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

$idUsuario = (int) $_SESSION["id_usuario"];
$carrinho = new Carrinho($conexao, $idUsuario);


if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["id_endereco"])) {
    $selecionados = array_values(array_unique(array_map(
        "intval",
        $_POST["selecionados"] ?? []
    )));

    if (count($selecionados) === 0) {
        $_SESSION["aviso"] = "Selecione pelo menos um produto para finalizar a compra.";
        header("Location: carrinho.php");
        exit;
    }

    foreach ($_POST["quantidades"] ?? [] as $id => $quantidade) {
        if (!$carrinho->alterarQuantidade((int) $id, (int) $quantidade)) {
            $_SESSION["aviso"] = "Confira as quantidades. Elas não podem ser maiores que o estoque.";
            header("Location: carrinho.php");
            exit;
        }
    }

    $_SESSION["compra_selecionada"] = $selecionados;
    header("Location: finalizar_compra.php");
    exit;
}

// Se veio um endereço salvo, finaliza a compra.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_endereco"])) {
    if (empty($_SESSION["compra_selecionada"])) {
        header("Location: carrinho.php");
        exit;
    }

    $idEndereco = (int) $_POST["id_endereco"];
    $idPedido = $carrinho->finalizar($_SESSION["compra_selecionada"], $idEndereco);

    if ($idPedido) {
        unset($_SESSION["compra_selecionada"]);
        $_SESSION["pedido_finalizado"] = $idPedido;
        $_SESSION["mensagem_compra"] = "Compra realizada com sucesso!";
        header("Location: compra_sucesso.php");
        exit;
    }

    $_SESSION["aviso"] = "Não foi possível finalizar a compra. Confira o endereço e o estoque.";
    header("Location: carrinho.php");
    exit;
}

if (!empty($_SESSION["endereco_escolhido"])) {
    if (empty($_SESSION["compra_selecionada"])) {
        unset($_SESSION["endereco_escolhido"]);
        header("Location: carrinho.php");
        exit;
    }

    $idEndereco = (int) $_SESSION["endereco_escolhido"];
    unset($_SESSION["endereco_escolhido"]);

    $idPedido = $carrinho->finalizar($_SESSION["compra_selecionada"], $idEndereco);

    if ($idPedido) {
        unset($_SESSION["compra_selecionada"]);
        $_SESSION["pedido_finalizado"] = $idPedido;
        $_SESSION["mensagem_compra"] = "Compra realizada com sucesso!";
        header("Location: compra_sucesso.php");
        exit;
    }

    $_SESSION["aviso"] = "Não foi possível finalizar a compra. Confira o endereço e o estoque.";
    header("Location: carrinho.php");
    exit;
}

if (empty($_SESSION["compra_selecionada"])) {
    header("Location: carrinho.php");
    exit;
}

$enderecos = [];
$sql = "SELECT * FROM enderecos WHERE id_usuario = ? ORDER BY id_endereco DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();

while ($endereco = $resultado->fetch_assoc()) {
    $enderecos[] = $endereco;
}
$stmt->close();

if (count($enderecos) === 0) {
    header("Location: endereco.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Endereço de entrega - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        main { max-width: 900px; margin: 35px auto; padding: 0 20px; }
        .endereco-opcao { display: block; padding: 14px 0; border-bottom: 1px solid #ddd; }
        .endereco-opcao p { margin: 6px 0 0 25px; }
        .acoes { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 22px; }
    </style>
</head>
<body>
<header>
    <img src="../img/logo.png" alt="Logo HQ Mania">
    <nav>
        <a href="../index.php"><img src="../img/inicio.png" alt="">Início</a>
        <a href="../produtos/catalogo.php"><img src="../img/catalogo.png" alt="">Catálogo</a>
        <a href="carrinho.php"><img src="../img/carrinho.png" alt="">Carrinho</a>
        <a href="../usuarios/perfil.php"><img src="../img/perfil.png" alt="">Perfil</a>
    </nav>
</header>

<main>
    <h2>Escolha o endereço de entrega</h2>

    <form method="POST" action="finalizar_compra.php">
        <?php foreach ($enderecos as $endereco): ?>
            <label class="endereco-opcao">
                <input type="radio" name="id_endereco"
                       value="<?php echo (int) $endereco["id_endereco"]; ?>"
                       required>
                <strong><?php echo htmlspecialchars($endereco["nome_endereco"]); ?></strong>
                <p>
                    <?php echo htmlspecialchars($endereco["rua"]); ?>,
                    <?php echo htmlspecialchars($endereco["numero"]); ?>
                    <?php if (!empty($endereco["complemento"])): ?>
                        - <?php echo htmlspecialchars($endereco["complemento"]); ?>
                    <?php endif; ?>
                    <br>
                    <?php echo htmlspecialchars($endereco["bairro"]); ?>,
                    <?php echo htmlspecialchars($endereco["cidade"]); ?> -
                    <?php echo htmlspecialchars($endereco["estado"]); ?>
                    <br>CEP: <?php echo htmlspecialchars($endereco["cep"]); ?>
                </p>
            </label>
        <?php endforeach; ?>

        <div class="acoes">
            <button type="submit">Usar este endereço e finalizar compra</button>
            <a href="endereco.php?novo=1">Cadastrar novo endereço</a>
        </div>
    </form>

    <p>Frete: grátis para todo o Brasil.</p>
</main>
</body>
</html>
