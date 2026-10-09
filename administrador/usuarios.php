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
//pesquisar usuarios por nome e email
$pesquisa = "";

if (isset($_GET["pesquisa"])) {
    $pesquisa = $_GET["pesquisa"];
}

if ($pesquisa != "") {

    $sql = "SELECT * FROM usuarios
            WHERE nome LIKE ?
            OR email LIKE ?
            ORDER BY id_usuario DESC";

    $stmt = $conexao->prepare($sql);

    $busca = "%" . $pesquisa . "%";

    $stmt->bind_param("ss", $busca, $busca);

} else {

    $sql = "SELECT * FROM usuarios
            ORDER BY id_usuario DESC";

    $stmt = $conexao->prepare($sql);
}


$stmt->execute();

$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <title>Usuários - HQ Mania</title>

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<header>

    <h1>HQ MANIA</h1>

    <nav>

        <a href="admindex.php">Início</a>
        <a href="../usuarios/cadastro.php">Cadastro de usuário</a>
        <a href="../usuarios/catalogo.php">Catálogo</a>
        <a href="usuarios.php">Usuários</a>
        <a href="../logout.php">Sair</a>

    </nav>

</header>

<main class="pagina-usuarios">

    <h2>ADMINISTRAÇÃO DE USUÁRIOS</h2>

    <form method="GET" class="pesquisa">

        <input type="text" name="pesquisa" placeholder="Pesquisar por nome ou e-mail" value="<?= $pesquisa ?>">
        
        <button type="submit">
            PESQUISAR
        </button>
        <a href="usuarios.php">Voltar</a>


    </form>

    <div class="tabela">

        <table>
            <thead>
                <tr>

                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Data de nascimento</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Cargo</th>
                    <th>Ações</th>

                </tr>
            </thead>

            <tbody>
                <?php while ($usuario = $resultado->fetch_assoc()) { ?>

                    <tr>
                        <td><?= $usuario["nome"] ?></td>
                        <td><?= $usuario["cpf"] ?></td>
                        <td><?= $usuario["data_nasc"] ?></td>
                        <td><?= $usuario["telefone"] ?></td>
                        <td><?= $usuario["email"] ?></td>
                        <td><?= $usuario["cargo"] ?></td>
                        <td><a href="editar.php?id=<?= $usuario["id_usuario"] ?>">Editar</a>

                            <?php if ($usuario["id_usuario"] != $_SESSION["id_usuario"]) { ?>
                                <a href="excluir.php?id=<?= $usuario["id_usuario"] ?>"
                                onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                    Excluir
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>

<footer>
</footer>

</body>

</html>