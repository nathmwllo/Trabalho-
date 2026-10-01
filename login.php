<?php

session_start();
require_once("conexao.php");

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM usuarios WHERE email = ?";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();


    if ($usuario) {
        if (password_verify($senha, $usuario["senha"])) {
            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["nome"] = $usuario["nome"];
            $_SESSION["cargo"] = $usuario["cargo"];

            if ($usuario["cargo"] == "Administrador") {
                header("Location:admindex.php");
            } else {
                header("Location:index.php");
            }
            exit;
        } else {
            $mensagem = "Senha incorreta.";
        }
    } else {
        $mensagem = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login - HQ Mania</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="formulario login">
        <h1>LOGIN</h1>

           <?php if ($mensagem != "") { ?>
            <p class="mensagem">
                <?= $mensagem ?>
            </p>

        <?php } ?>
        <form method="POST">
            <label>E-mail</label>
            <input type="email" name="email" required placeholder="exemplo@dominio.com">

            <label>Senha</label>
            <input type="password" name="senha" required >

            <button type="submit">
                ENTRAR
            </button>

        </form>
        <p>
            Ainda não tem uma conta?
            <a href="cadastro.php">
                Cadastre-se
            </a>
        </p>

    </div>

</body>

</html>