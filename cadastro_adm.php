<?php

session_start();

require_once("conexao.php");

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $data_nasc = $_POST["data_nasc"];
    $telefone = $_POST["telefone"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM usuarios WHERE email = ?";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();


    if ($resultado->num_rows > 0) {
        $mensagem = "Esse e-mail já está cadastrado.";
    } else {

        $senha_hash = password_hash(
            $senha,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO usuarios
        (nome, cpf, data_nasc, telefone, email, senha, cargo)
        VALUES (?, ?, ?, ?, ?, ?, 'Cliente')";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "ssssss",
            $nome,
            $cpf,
            $data_nasc,
            $telefone,
            $email,
            $senha_hash
        );
        $stmt->execute();
        $mensagem = "Cadastro realizado com sucesso!";
        header("Location: index.php");
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro - HQ Mania</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="formulario">
        <h1>CRIE UMA CONTA</h1>
        <?php if ($mensagem != "") { ?>

            <p class="mensagem">
                <?= $mensagem ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Nome</label>
            <input type="text" name="nome">

            <label>CPF</label>
            <input type="text" name="cpf" pattern="[0-9]+" title="Apenas números são permitidos.">

            <label>Data de nascimento</label>
            <input type="date" name="data_nasc" >
            
            <label>Telefone</label>
            <input type="text" name="telefone" pattern="[0-9]+" title="Apenas números são permitidos.">

            <label>E-mail</label>
            <input type="email" name="email" placeholder="exemplo@dominio.com">

            <label>Senha</label>
            <input type="password" name="senha">

            <button type="submit">
                CRIAR CONTA
            </button>

        </form>

        <p>
            Já possui uma conta?
            <a href="login.php">
                Faça login
            </a>
        </p>
         
        <p>Deseja voltar para o início? <a href="admindex.php">Clique aqui</a></p>
    </div>

</body>

</html>