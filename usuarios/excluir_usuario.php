
<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$id = (int) $_SESSION["id_usuario"];
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (($_POST["confirmar"] ?? "") === "sim") {

        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows === 1) {
            $stmt->close();

            $_SESSION = [];
            session_destroy();

            header("Location: ../index.php");
            exit;
        }

        $erro = "Não foi possível excluir sua conta.";
        $stmt->close();

    } else {
        header("Location: perfil.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir conta - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<header>
        <img src="../img/logo.png" alt="Logo HQ Mania">
</header>

<main>
    <section class="formulario">

        <h1>Excluir conta</h1>

        <?php if ($erro !== ""): ?>
            <p class="mensagem">
                <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <p>Tem certeza de que deseja excluir sua conta?</p>

        <p>
            Essa ação pode ser permanente e você poderá perder o acesso
            aos seus dados.
        </p>

        <form method="POST" action="excluir_usuario.php"
              onsubmit="return confirm('Tem certeza de que deseja excluir sua conta? Essa ação não pode ser desfeita.');">

            <input type="hidden" name="confirmar" value="sim">

            <button type="submit">Sim, excluir minha conta</button>

        </form>

        <p>
            <a href="perfil.php">Não, quero voltar</a>
        </p>

    </section>
</main>

<footer></footer>

</body>
</html>
