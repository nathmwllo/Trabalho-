<?php
// cadastro de usuarios pelo adm
session_start();

require_once "conexao.php";
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $nome = trim($_POST["nome"]);
    $email = $_POST["email"];
    $telefone = trim($_POST["telefone"]);
    $data_nasc = trim($_POST["data_nasc"]);
    $cpf = trim($_POST["cpf"]);
    $senha = $_POST["senha"];

    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){

        $mensagem = "Este e-mail já está cadastrado.";
    }else{
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    

    $sql = "INSERT INTO usuarios
    (nome, cpf, data_nasc, telefone, email, senha , cargo)
    VALUES (?, ?, ?, ?, ?, ?, Cliente)";

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
        $mensagem = "Usuário cadastrado com sucesso";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>Cadastro de Usuários</title>
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
            <input type="text" name="nome" >

            <label>CPF</label>
            <input type="text" name="cpf" pattern="[0-9]+" title="Apenas números são permitidos.">

            <label>Data de nascimento</label>
            <input type="date" name="data_nasc">
            
            <label>Telefone</label>
            <input type="text" name="telefone" pattern="[0-9]+"  title="Apenas números são permitidos.">

            <label>E-mail</label>
            <input type="email" name="email"  placeholder="exemplo@dominio.com">

            <label>Senha</label>
            <input type="password" name="senha" >

            <button type="submit">
                CRIAR CONTA
            </button>
            
            <button href="index.php">
                VOLTAR AO INÍCIO
            </button>
        </form>
    </div>
</body>
</html>