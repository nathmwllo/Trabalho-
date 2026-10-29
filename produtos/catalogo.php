<?php
session_start();
require_once "../conexao.php";

// Pesquisa de produtos
$pesquisa = trim($_GET["pesquisa"] ?? "");

$sql = "SELECT * FROM produtos
        WHERE nome_produto LIKE ? OR editora LIKE ?
        ORDER BY id_produto DESC";

$stmt = $conexao->prepare($sql);
$termo = "%" . $pesquisa . "%";
$stmt->bind_param("ss", $termo, $termo);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - HQ Mania</title>
    <link rel="stylesheet" href="../style.css">
</head>

<style>
  details[open] summary .texto-botao {
    display: none;
  }
  details[open] summary::after {
    content: "Ver menos";
    cursor: pointer;
  }
   .perfil-menu {
    position: relative;
    display: inline-block;
}

.dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 180px;
    margin-top: 8px;
    padding: 6px 0;
    background: #e9e1d3;          
    border: 3px solid #000;      
    border-radius: 10px;
    box-shadow: 4px 4px 0 #000;  
    z-index: 100;
}

.dropdown.aberto {
    display: block;
}

nav .dropdown a {
    display: flex;
    flex-direction: row;       
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    width: auto;
    height: auto;
    margin: 0;
    padding: 10px 16px;
    background: transparent;
    border: none;
    border-radius: 0;
    box-shadow: none;
    color: #000;
    font-size: 14px;
    text-align: left;
    text-decoration: none;
}

nav .dropdown a img {
    width: 18px !important;
    height: 18px !important;
    margin: 0 !important;
    flex-shrink: 0;
}

nav .dropdown a:hover {
    background: #a89c5f;         
}

nav .dropdown a + a {
    border-top: 1px solid #00000030;
}
    .voltar{
    padding: 12px 20px;
    font-size: 14px;
    background: #9d8b55;
    border: 3px solid black;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    color: #111;
    }
</style>
<body>

    <header>
            <img src="../img/logo.png" alt="logo">
            <nav>
                <a href="../usuarios/login.php" <?php if (isset($_SESSION['id_usuario'])) echo 'style="display:none"'; ?>>
                    <img src="../img/login.png" alt="">Login</a>
                <a href="../index.php">
                    <img src="../img/inicio.png" alt="inicio"><span>Início</span></a>
                <a href="catalogo.php">
                    <img src="../img/catalogo.png" alt="catálogo">Catálogo</a>
                <a href="#">
                    <img src="../img/carrinho.png" alt="">Carrinho</a>
                 <div class="perfil-menu">
                   <a href="../usuarios/perfil.php" id="btn-perfil"><img src="../img/perfil.png" alt="perfil">Perfil</a>

            <div class="dropdown" id="dropdown-perfil">
                <?php if (isset($_SESSION['id_usuario'])): ?>
                 <a href="../usuarios/perfil.php"> <img src="../img/perfil.png" alt="perfil">Minha conta</a>
                 <a href="../usuarios/logout.php"><img src="../img/logout.png" alt="sair">Logout</a>
            <?php else: ?>
                 <a href="../usuarios/login.php"><img src="../img/login.png" alt="login">Fazer login</a>
            <?php endif; ?>
             </div>
            </div>
            </nav>
        </header>

    <main class="pagina-usuarios">
    <h2>Catálogo de quadrinhos</h2>
    
    <form method="GET" action="catalogo.php" class="pesquisa">
        <input type="text" name="pesquisa" placeholder="Pesquisar quadrinho ou editora" value="<?= htmlspecialchars($pesquisa) ?>">
        <button type="submit">Pesquisar</button>
        <a href="catalogo.php" class="voltar">Voltar</a>
    </form>
    
    <hr>
    
    <?php if ($resultado->num_rows > 0): ?>
        <?php while ($produto = $resultado->fetch_assoc()): ?>
    
            <div>
                <?php if (!empty($produto["imagem"])): ?>
                    <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="Capa do quadrinho" width="150">
                <?php endif; ?>
    
                <h3><?= htmlspecialchars($produto["nome_produto"]) ?></h3>
                <p>Edição: <?= htmlspecialchars($produto["edicao"]) ?></p>
                <p>Preço: R$ <?= number_format($produto["preco"], 2, ",", ".") ?></p>
    
                <?php if ($produto["estoque"] > 0): ?>
                    <p>Disponível em estoque</p>
    
                    <form action="adicionar_carrinho.php" method="POST">
                        <input type="hidden" name="id_produto" value="<?= (int) $produto["id_produto"] ?>">
                        <input type="hidden" name="quantidade" value="1">
                        <button type="submit">Adicionar ao carrinho</button>
                    </form>
                <?php else: ?>
                    <p>Indisponível em estoque</p>
                <?php endif; ?>
    
                <details>
                    <summary><span class="texto-botao" style="cursor: pointer;">Ver mais</span></summary>
                    <?php
                        echo "<p>Editora: " . htmlspecialchars($produto["editora"]) . "</p>";
                        echo "<p>Sinopse: " . nl2br(htmlspecialchars($produto["sinopse"])) . "</p>";
                    ?>
                </details>
            </div>
    
            <hr>
    
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhum quadrinho encontrado.</p>
    <?php endif; ?>

</main>
<footer></footer>

<script>
    const btnPerfil = document.getElementById('btn-perfil');
    const dropdown = document.getElementById('dropdown-perfil');

    btnPerfil.addEventListener('click', function (e) {
        e.preventDefault();          
        dropdown.classList.toggle('aberto');
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.perfil-menu')) {
            dropdown.classList.remove('aberto');
        }
    });
</script>

</body>
</html>
<?php
$stmt->close();
$conexao->close();
?>
