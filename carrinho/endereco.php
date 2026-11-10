<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../conexao.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../usuarios/login.php");
    exit;
}

if (empty($_SESSION["compra_selecionada"])) {
    header("Location: carrinho.php");
    exit;
}

$idUsuario = (int) $_SESSION["id_usuario"];
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome_endereco"] ?? "");
    $cep = trim($_POST["cep"] ?? "");
    $rua = trim($_POST["rua"] ?? "");
    $numero = trim($_POST["numero"] ?? "");
    $complemento = trim($_POST["complemento"] ?? "");
    $bairro = trim($_POST["bairro"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");
    $estado = strtoupper(trim($_POST["estado"] ?? ""));

    if ($nome === "" || $cep === "" || $rua === "" || $numero === "" ||
        $bairro === "" || $cidade === "" || $estado === "") {
        $erro = "Preencha todos os campos obrigatórios.";
    } elseif (strlen(preg_replace('/\D/', '', $cep)) !== 8) {
        $erro = "Digite um CEP válido.";
    } elseif (strlen($estado) !== 2) {
        $erro = "Informe a sigla do estado com duas letras.";
    } else {
        $sql = "INSERT INTO enderecos
                (id_usuario, nome_endereco, cep, rua, numero, complemento, bairro, cidade, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param(
            "issssssss",
            $idUsuario,
            $nome,
            $cep,
            $rua,
            $numero,
            $complemento,
            $bairro,
            $cidade,
            $estado
        );

        if ($stmt->execute()) {
            $_SESSION["endereco_escolhido"] = (int) $conexao->insert_id;
            $stmt->close();
            header("Location: finalizar_compra.php");
            exit;
        } else {
            $erro = "Não foi possível salvar o endereço. Tente novamente.";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar endereço - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        main { max-width: 650px; margin: 35px auto; padding: 0 20px; }
        .campo-endereco { display: flex; flex-direction: column; margin-bottom: 13px; }
        .campo-endereco input { padding: 10px; }
        .mensagem-erro { color: #b00020; margin-bottom: 15px; }
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
    <h2>Cadastrar endereço de entrega</h2>

    <?php if ($erro !== ""): ?>
        <p class="mensagem-erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <form method="POST" action="endereco.php?novo=1">
        <div class="campo-endereco">
            <label for="nome_endereco">Nome do endereço (ex.: Casa ou Trabalho)</label>
            <input type="text" id="nome_endereco" name="nome_endereco"
                   value="<?php echo htmlspecialchars($_POST["nome_endereco"] ?? ""); ?>" required>
        </div>

        <div class="campo-endereco">
            <label for="cep">CEP</label>
            <input type="text" id="cep" name="cep" placeholder="00000-000"
                   maxlength="9" value="<?php echo htmlspecialchars($_POST["cep"] ?? ""); ?>" required>
        </div>

        <div class="campo-endereco">
            <label for="rua">Rua</label>
            <input type="text" id="rua" name="rua"
                   value="<?php echo htmlspecialchars($_POST["rua"] ?? ""); ?>" required>
        </div>

        <div class="campo-endereco">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero"
                   value="<?php echo htmlspecialchars($_POST["numero"] ?? ""); ?>" required>
        </div>

        <div class="campo-endereco">
            <label for="complemento">Complemento</label>
            <input type="text" id="complemento" name="complemento"
                   value="<?php echo htmlspecialchars($_POST["complemento"] ?? ""); ?>">
        </div>

        <div class="campo-endereco">
            <label for="bairro">Bairro</label>
            <input type="text" id="bairro" name="bairro"
                   value="<?php echo htmlspecialchars($_POST["bairro"] ?? ""); ?>" required>
        </div>

        <div class="campo-endereco">
            <label for="cidade">Cidade</label>
            <input type="text" id="cidade" name="cidade"
                   value="<?php echo htmlspecialchars($_POST["cidade"] ?? ""); ?>" required>
        </div>

        <div class="campo-endereco">
            <label for="estado">Estado (UF)</label>
            <input type="text" id="estado" name="estado" maxlength="2"
                   value="<?php echo htmlspecialchars($_POST["estado"] ?? ""); ?>" required>
        </div>

        <button type="submit">Salvar endereço e continuar</button>
    </form>
</main>

<script>
document.getElementById("cep").addEventListener("blur", function () {
    const campoCep = this;
    const cep = campoCep.value.replace(/\D/g, "");

    if (cep.length === 0) return;

    if (cep.length !== 8) {
        alert("Digite um CEP válido com 8 números.");
        return;
    }

    fetch("https://viacep.com.br/ws/" + cep + "/json/")
        .then(function (resposta) {
            if (!resposta.ok) throw new Error("Falha na consulta");
            return resposta.json();
        })
        .then(function (dados) {
            if (dados.erro) {
                alert("CEP não encontrado. Confira o número ou preencha o endereço manualmente.");
                return;
            }

            document.getElementById("rua").value = dados.logradouro || "";
            document.getElementById("bairro").value = dados.bairro || "";
            document.getElementById("cidade").value = dados.localidade || "";
            document.getElementById("estado").value = dados.uf || "";
        })
        .catch(function () {
            alert("Não foi possível consultar o CEP. Você pode preencher o endereço manualmente.");
        });
});
</script>
</body>
</html>
