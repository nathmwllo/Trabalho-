<?php
session_start();
require_once("conexao.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["cargo"] != "Administrador") {
    header("Location: index.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Produto inválido.");
}

$id = (int) $_GET["id"];


$sql = "SELECT * FROM produtos WHERE id_produto = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();
$produto = $resultado->fetch_assoc();

if (!$produto) {
    die("Produto não encontrado.");
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome_produto"] ?? "");
    $editora = trim($_POST["editora"] ?? "");
    $edicao = trim($_POST["edicao"] ?? "");
    $sinopse = trim($_POST["sinopse"] ?? "");
    $preco = $_POST["preco"] ?? "";
    $estoque = $_POST["estoque"] ?? "";

    if (
        $nome === "" ||
        $preco === "" ||
        $estoque === "" ||
        !is_numeric($preco) ||
        !is_numeric($estoque) ||
        (float) $preco < 0 ||
        (int) $estoque < 0 ||
        (float) $estoque != (int) $estoque
    ) {
        $erro = "Preencha os campos corretamente.";
    } else {

        $preco = (float) $preco;
        $estoque = (int) $estoque;
        $caminho = $produto["imagem"];

        if (
            isset($_FILES["imagem"]) &&
            $_FILES["imagem"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {
            if ($_FILES["imagem"]["error"] !== UPLOAD_ERR_OK) {
                $erro = "Erro ao enviar a imagem.";
            } else {
                $tipo = mime_content_type($_FILES["imagem"]["tmp_name"]);

                $tiposPermitidos = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($tiposPermitidos[$tipo])) {
                    $erro = "Use uma imagem JPG, PNG ou WEBP.";
                } else {
                    $pasta = "uploads/";

                    if (!is_dir($pasta)) {
                        mkdir($pasta, 0755, true);
                    }

                    $nomeArquivo = uniqid("hq_", true) . "." .
                        $tiposPermitidos[$tipo];

                    $novoCaminho = $pasta . $nomeArquivo;

                    if (
                        move_uploaded_file(
                            $_FILES["imagem"]["tmp_name"],
                            $novoCaminho
                        )
                    ) {
                        $caminho = $novoCaminho;
                    } else {
                        $erro = "Não foi possível salvar a imagem.";
                    }
                }
            }
        }

        if ($erro === "") {
            $sql = "UPDATE produtos
                    SET nome_produto = ?,
                        editora = ?,
                        edicao = ?,
                        sinopse = ?,
                        preco = ?,
                        estoque = ?,
                        imagem = ?
                    WHERE id_produto = ?";

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                "ssssdisi",
                $nome,
                $editora,
                $edicao,
                $sinopse,
                $preco,
                $estoque,
                $caminho,
                $id
            );

            if ($stmt->execute()) {
                header("Location: listagem.php");
                exit;
            } else {
                $erro = "Erro ao atualizar o produto.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar produto - HQ Mania</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<main class="pagina-usuarios">

    <h2>EDITAR PRODUTO</h2>

    <?php if ($erro !== ""): ?>
        <p><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="POST" class="formulario" enctype="multipart/form-data">
        <label>Nome do quadrinho</label>
        <input type="text" name="nome_produto" value="<?= htmlspecialchars($_POST["nome_produto"] ?? $produto["nome_produto"] ) ?>" required>

        <label>Editora</label>
        <input type="text" name="editora" value="<?= htmlspecialchars( $_POST["editora"] ?? $produto["editora"] ?? "" ) ?>" >

        <label>Edição</label>
        <input type="text" name="edicao" value="<?= htmlspecialchars($_POST["edicao"] ?? $produto["edicao"] ?? ""  ) ?>">

        <label>Sinopse</label>
        <textarea name="sinopse" rows="5"><?= htmlspecialchars(
            $_POST["sinopse"] ?? $produto["sinopse"] ?? ""
        ) ?></textarea>

        <label>Preço (R$)</label>
        <input type="number" name="preco" min="0" step="0.01" value="<?= htmlspecialchars( (string) ($_POST["preco"] ?? $produto["preco"]) ) ?>" required>

        <label>Estoque</label>
        <input type="number" name="estoque" min="0" step="1" value="<?= htmlspecialchars((string) ($_POST["estoque"] ?? $produto["estoque"])) ?>" required>

        <label>Imagem atual</label>

        <?php if (!empty($produto["imagem"])): ?>
            <p>
                <img src="<?= htmlspecialchars($produto["imagem"]) ?>" alt="Capa atual" width="120">
            </p>
        <?php else: ?>
            <p>Nenhuma imagem cadastrada.</p>
        <?php endif; ?>

        <label>Escolher nova imagem (opcional)</label>
        <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp" >

        <p>Se não escolher outra imagem, a capa atual será mantida.</p>

        <button type="submit">SALVAR ALTERAÇÕES</button>

        <a href="listagem.php">Cancelar</a>

    </form>

</main>

<footer>
</footer>

</body>
</html>
```