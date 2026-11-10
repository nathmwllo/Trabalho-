<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["pedido_finalizado"])) {
    header("Location: carrinho.php");
    exit;
}

$idPedido = (int) $_SESSION["pedido_finalizado"];
$mensagem = $_SESSION["mensagem_compra"] ?? "Compra realizada com sucesso!";

unset($_SESSION["pedido_finalizado"], $_SESSION["mensagem_compra"]);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra realizada - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        main { max-width: 750px; margin: 70px auto; padding: 0 20px; text-align: center; }
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
    <h1><?php echo htmlspecialchars($mensagem); ?></h1>
    <p>Seu pedido foi registrado na HQ Mania.</p>
    <p>Número do pedido: <strong><?php echo $idPedido; ?></strong></p>
    <p>Frete grátis para todo o Brasil!</p>
    <a href="../index.php">Voltar para a página inicial</a>
</main>
</body>
</html>
