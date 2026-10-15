
<?php
session_start();
require_once "../conexao.php";

$mensagem = "";

if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["cargo"]) ||$_SESSION["cargo"] != "Administrador") {
    header("Location: ../usuarios/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome_produto"]);
    $editora = trim($_POST["editora"]);
    $edicao = trim($_POST["edicao"]);
    $sinopse = trim($_POST["sinopse"]);
    $preco = $_POST["preco"];
    $estoque = $_POST["estoque"];

   
    if ($nome == "" || $editora == "" || $edicao == "" || $sinopse == "" || $preco == "" || $estoque == "") {
        $mensagem = "Preencha todos os campos.";
   
        } elseif ( !isset($_FILES["imagem"]) || $_FILES["imagem"]["error"] != UPLOAD_ERR_OK) {
        $mensagem = "Selecione uma imagem válida.";

        } elseif (!is_numeric($preco) || $preco < 0 || filter_var($estoque, FILTER_VALIDATE_INT) === false || $estoque < 0) {
        $mensagem = "Preço ou estoque inválido.";
       
        } else { 

        $temporario = $_FILES["imagem"]["tmp_name"];
        $tipo = mime_content_type($temporario);

        $tiposPermitidos = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp"
        ];

        if (!isset($tiposPermitidos[$tipo])) {

            $mensagem = "Use uma imagem JPG, PNG ou WEBP.";
        } elseif ($_FILES["imagem"]["size"] > 2 * 1024 * 1024) {

            $mensagem = "A imagem deve ter no máximo 2 MB.";
        } else {

            $pasta = "uploads/";

            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }

            $nomeImagem = uniqid("hq_", true) . "." .
                          $tiposPermitidos[$tipo];

            $caminho = $pasta . $nomeImagem;

            if (move_uploaded_file($temporario, $caminho)) {

                $sql = "INSERT INTO produtos
                (nome_produto, editora, edicao, sinopse,
                 preco, estoque, imagem)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conexao->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param(
                        "ssssdis",
                        $nome,
                        $editora,
                        $edicao,
                        $sinopse,
                        $preco,
                        $estoque,
                        $caminho
                    );

                    if ($stmt->execute()) {
                        $mensagem = "Produto cadastrado com sucesso!";
                    } else {
                        unlink($caminho);
                        $mensagem = "Erro ao cadastrar o produto.";
                    }

                    $stmt->close();
                } else {
                    unlink($caminho);
                    $mensagem = "Erro ao preparar o cadastro.";
                }

            } else {
                $mensagem = "Não foi possível salvar a imagem.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar produto - HQ Mania</title>
</head>
<body>

    <h1>Cadastro de Produtos - HQ Mania</h1>

    <p>
        <?php echo htmlspecialchars($mensagem); ?>
    </p>

    <form method="POST" enctype="multipart/form-data">

        <label>Nome da HQ:</label><br>
        <input type="text" name="nome_produto" required>
        <br><br>

        <label>Editora:</label><br>
        <input type="text" name="editora" required>
        <br><br>

        <label>Edição:</label><br>
        <input type="text" name="edicao" required>
        <br><br>

        <label>Sinopse:</label><br>
        <textarea name="sinopse" required></textarea>
        <br><br>

        <label>Preço (R$):</label><br>
        <input type="number" name="preco"
               min="0" step="0.01" required>
        <br><br>

        <label>Quantidade em estoque:</label><br>
        <input type="number" name="estoque"
               min="0" step="1" required>
        <br><br>

        <label>Capa da HQ:</label><br>
        <input type="file" name="imagem"
               accept=".jpg,.jpeg,.png,.webp" required>
        <br><br>

        <button type="submit">Cadastrar produto</button>

    </form>

    <br>
    <a href="listagem.php">Ver produtos cadastrados</a>

</body>
</html>
