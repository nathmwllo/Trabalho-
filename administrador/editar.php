<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION["cargo"] != "Administrador") {
    header("Location: ../index.php");
    exit;
}

$id = $_GET["id"];

$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    die("Usuário não encontrado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $data_nasc = $_POST["data_nasc"];
    $telefone = $_POST["telefone"];
    $email = $_POST["email"];

    $sql = "UPDATE usuarios
            SET nome = ?,
                cpf = ?,
                data_nasc = ?,
                telefone = ?,
                email = ?
            WHERE id_usuario = ?";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param(
        "sssssi",
        $nome,
        $cpf,
        $data_nasc,
        $telefone,
        $email,
        $id
    );

    $stmt->execute();

    if (!empty($_POST["senha"])) {

        $senha = password_hash(
            $_POST["senha"],
            PASSWORD_DEFAULT
        );

        $sql = "UPDATE usuarios
                SET senha = ?
                WHERE id_usuario = ?";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "si",
            $senha,
            $id
        );

        $stmt->execute();
    }
    header("Location: usuarios.php");

    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Editar usuário - HQ Mania</title>

<link rel="stylesheet" href="../style.css">

</head>

<body>

<header>

<h1>HQ MANIA</h1>

<nav>

<a href="admindex.php">Início</a>

<a href="../usuarios/cadastro.php">
Cadastro de usuário
</a>

<a href="../usuarios/catalogo.php">Catálogo</a>
<a href="usuarios.php">Usuários</a>
<a href="../logout.php">Sair</a>

</nav>

</header>

<main class="pagina-usuarios">

<h2>EDITAR USUÁRIO</h2>


<form method="POST" class="form-editar">

<label>Nome</label>

<input
    type="text"
    name="nome"
    value="<?= $usuario["nome"] ?>"
    required
>


<label>CPF</label>

<input
    type="text"
    name="cpf"
    value="<?= $usuario["cpf"] ?>"
    required
>


<label>Data de nascimento</label>

<input
    type="date"
    name="data_nasc"
    value="<?= $usuario["data_nasc"] ?>"
    required
>


<label>Telefone</label>

<input
    type="text"
    name="telefone"
    value="<?= $usuario["telefone"] ?>"
    required
>


<label>E-mail</label>

<input
    type="email"
    name="email"
    value="<?= $usuario["email"] ?>"
    required
>


<label>Nova senha</label>

<input
    type="password"
    name="senha"
    placeholder="Deixe vazio para manter a senha atual"
>


<label>Cargo</label>

<input
    type="text"
    value="<?= $usuario["cargo"] ?>"
    readonly
>


<button type="submit">
SALVAR ALTERAÇÕES
</button>

<a href="usuarios.php">
Cancelar
</a>

</form>

</main>


<footer>
</footer>

</body>

</html>