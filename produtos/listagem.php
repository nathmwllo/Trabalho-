<?php
session_start();
require_once "../conexao.php";

if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["cargo"]) || $_SESSION["cargo"] !== "Administrador") {
    header("Location: ../usuarios/login.php");
    exit;
}

$sql = "SELECT * FROM produtos ORDER BY id_produto DESC";
$resultado = $conexao->query($sql);

if (!$resultado) {
    die("Erro ao buscar produtos: " . $conexao->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Produtos - HQ Mania</title>

</head>

<body>
    <header>
    <img src="../img/logo.png" alt="">

    <nav>
        <a href="admindex.php">Início</a>
        <a href="../produtos/listagem.php">Produtos</a>
        <a href="usuarios.php">Usuários</a>
        <a href="../usuarios/logout.php">Sair</a>
    </nav>
    </header>

    <h1>Gerenciar Produtos</h1>

    <div class="topo">
        <a class="botao" href="cadastro_produtos.php">
            Cadastrar novo produto
        </a>
    </div>

    <div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Capa</th>
                    <th>Nome</th>
                    <th>Editora</th>
                    <th>Edição</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($resultado->num_rows > 0): ?>

                    <?php while ($produto = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= (int) $produto["id_produto"] ?>
                            </td>

                            <td>
                                <?php if (!empty($produto["imagem"])): ?>
                                    <img
                                        class="capa"
                                        src="<?= htmlspecialchars($produto["imagem"]) ?>"
                                        alt="Capa do quadrinho"
                                    >
                                <?php else: ?>
                                    Sem capa
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($produto["nome_produto"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($produto["editora"] ?? "") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($produto["edicao"] ?? "") ?>
                            </td>

                            <td>
                                R$ <?= number_format(
                                    (float) $produto["preco"],
                                    2,
                                    ",",
                                    "."
                                ) ?>
                            </td>

                            <td>
                                <?= (int) $produto["estoque"] ?>
                            </td>

                            <td>
                                <a
                                    href="editar_produto.php?id=<?= (int) $produto["id_produto"] ?>"
                                >
                                    Editar
                                </a>

                                <br><br>

                                <a
                                    href="excluir_produto.php?id=<?= (int) $produto["id_produto"] ?>"
                                    onclick="return confirm('Deseja realmente excluir este produto?')"
                                >
                                    Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            Nenhum produto cadastrado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
