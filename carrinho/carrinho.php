<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../conexao.php";
require_once "carrinho_funcoes.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$carrinho = new Carrinho($conexao, $_SESSION["id_usuario"]);

if (isset($_POST["atualizar_ajax"])) {
    header("Content-Type: application/json; charset=utf-8");
    $ok = $carrinho->alterarQuantidade(
        (int) $_POST["id_produto"],
        (int) $_POST["quantidade"]
    );
    echo json_encode(["ok" => $ok]);
    exit;
}

if (isset($_SESSION["produto_adicionar"])) {
    $adicionado = $carrinho->adicionar((int) $_SESSION["produto_adicionar"]);
    unset($_SESSION["produto_adicionar"]);
}

if (isset($_POST["adicionar"])) {
    $carrinho->adicionar((int) $_POST["id_produto"]);

    header("Location: carrinho.php");
    exit;
}

if (isset($_POST["remover"])) {
    $carrinho->remover((int) $_POST["remover"]);
    header("Location: carrinho.php");
    exit;
}

$itens = $carrinho->listar();
$total = 0;

foreach ($itens as $item) {
    $total += $item["subtotal"];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">

</head>
<body>

<?php if (isset($_SESSION["aviso"])): ?>
    <script>
        alert(<?php echo json_encode($_SESSION["aviso"], JSON_UNESCAPED_UNICODE); ?>);
    </script>
    <?php unset($_SESSION["aviso"]); ?>
<?php endif; ?>

<header>
    <img src="../img/logo.png" alt="Logo HQ Mania">
    <nav>
        <a href="../index.php"><img src="../img/inicio.png" alt="">Início</a>
        <a href="../produtos/catalogo.php"><img src="../img/catalogo.png" alt="">Catálogo</a>
        <a href="carrinho.php"><img src="../img/carrinho.png" alt="">Carrinho</a>
        <a href="../usuarios/perfil.php"><img src="../img/perfil.png" alt="">Perfil</a>
    </nav>
</header>

<main class="pagina-carrinho">
    <h2>Meu carrinho</h2>

    <?php if (count($itens) > 0): ?>
        <form method="POST" action="finalizar_compra.php" id="form-carrinho" class="layout-carrinho">
            <section class="lista-carrinho">
                <h3 class="titulo-lista">Todos os itens (<?php echo count($itens); ?>)</h3>

                <?php foreach ($itens as $item): ?>
                    <div class="produto-carrinho">
                        <input class="selecao-produto" type="checkbox"
                               name="selecionados[]"
                               value="<?php echo (int) $item["id_produto"]; ?>"
                               checked>

                        <img src="../produtos/<?php echo htmlspecialchars($item["imagem"]); ?>"
                             alt="<?php echo htmlspecialchars($item["nome_produto"]); ?>">

                        <div class="informacoes-produto">
                            <h3 class="nome-produto"><?php echo htmlspecialchars($item["nome_produto"]); ?></h3>
                            <p class="preco-produto">R$ <?php echo number_format($item["preco"], 2, ",", "."); ?></p>

                            <div class="linha-produto">
                                <label class="quantidade">Qtd:
                                    <input type="number"
                                           class="campo-quantidade"
                                           data-preco="<?php echo (float) $item["preco"]; ?>"
                                           data-salvo="<?php echo (int) $item["quantidade"]; ?>"
                                           data-id="<?php echo (int) $item["id_produto"]; ?>"
                                           name="quantidades[<?php echo (int) $item["id_produto"]; ?>]"
                                           value="<?php echo (int) $item["quantidade"]; ?>"
                                           min="1"
                                           max="<?php echo (int) $item["estoque"]; ?>"
                                           required>
                                </label>

                                <p class="subtotal">Subtotal: R$ <span class="subtotal-item"><?php echo number_format($item["subtotal"], 2, ",", "."); ?></span></p>

                                <button type="submit" name="remover" class="botao-remover"
                                        value="<?php echo (int) $item["id_produto"]; ?>"
                                        formaction="carrinho.php" formmethod="POST" formnovalidate>
                                    Remover
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <aside class="resumo-pedido">
                <h3>Resumo do pedido</h3>

                <div class="linha-resumo">
                    <span>Itens</span>
                    <span><?php echo count($itens); ?></span>
                </div>

                <p class="total-carrinho">
                    <span>Total</span>
                    <strong>R$ <span id="total-carrinho"><?php echo number_format($total, 2, ",", "."); ?></span></strong>
                </p>

                <button type="submit" class="botao-finalizar">Finalizar compra</button>
            </aside>
        </form>
    <?php else: ?>
        <p class="carrinho-vazio">Seu carrinho está vazio.</p>
    <?php endif; ?>
</main>

<script>
const fmt = v => v.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
const campos = document.querySelectorAll('.campo-quantidade');

function recalcular() {
    let total = 0;
    campos.forEach(c => {
        const subtotal = c.value * c.dataset.preco;
        c.closest('.produto-carrinho').querySelector('.subtotal-item').textContent = fmt(subtotal);
        total += subtotal;
    });
    document.getElementById('total-carrinho').textContent = fmt(total);
}

campos.forEach(campo => {
    campo.addEventListener('change', async () => {
        if (!campo.checkValidity()) {
            if (campo.validity.rangeOverflow) {
                alert('Não há essa quantidade em estoque. Disponível: ' + campo.max + ' unidade(s).');
            }
            campo.value = campo.dataset.salvo;
            return recalcular();
        }

        const dados = new URLSearchParams({
            atualizar_ajax: 1,
            id_produto: campo.dataset.id,
            quantidade: campo.value
        });

        try {
            const r = await fetch('carrinho.php', { method: 'POST', body: dados, keepalive: true });
            const res = await r.json();
            if (res.ok) {
                campo.dataset.salvo = campo.value;
            } else {
                alert('Não há essa quantidade em estoque. Disponível: ' + campo.max + ' unidade(s).');
                campo.value = campo.dataset.salvo;
            }
        } catch (e) {
            alert('Não foi possível atualizar a quantidade.');
            campo.value = campo.dataset.salvo;
        }
        recalcular();
    });

    // Enter não deve acionar o botão "Remover"
    campo.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); campo.blur(); }
    });
});
</script>
</body>
</html>