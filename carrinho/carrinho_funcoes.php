<?php
class Carrinho {
    private $conexao;
    private $idUsuario;
    private $idCarrinho;

    public function __construct($conexao, $idUsuario) {
        $this->conexao = $conexao;
        $this->idUsuario = (int) $idUsuario;

        $sql = "SELECT id_carrinho FROM carrinho WHERE id_usuario = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("i", $this->idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $dados = $resultado->fetch_assoc();
            $this->idCarrinho = (int) $dados["id_carrinho"];
            $stmt->close();
        } else {
            $stmt->close();

            $sql = "INSERT INTO carrinho (data_carrinho, id_usuario)
                    VALUES (CURDATE(), ?)";
            $stmtNovo = $this->conexao->prepare($sql);
            $stmtNovo->bind_param("i", $this->idUsuario);
            $stmtNovo->execute();
            $this->idCarrinho = (int) $this->conexao->insert_id;
            $stmtNovo->close();
        }
    }

    public function adicionar($idProduto) {
        $idProduto = (int) $idProduto;

        $sql = "SELECT estoque FROM produtos WHERE id_produto = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("i", $idProduto);
        $stmt->execute();
        $produto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$produto || (int) $produto["estoque"] < 1) {
            return false;
        }

        $sql = "SELECT quantidade FROM item_carrinho
                WHERE id_carrinho = ? AND id_produto = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("ii", $this->idCarrinho, $idProduto);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($item) {
            $quantidade = (int) $item["quantidade"] + 1;

            if ($quantidade > (int) $produto["estoque"]) {
                return false;
            }

            $sql = "UPDATE item_carrinho SET quantidade = ?
                    WHERE id_carrinho = ? AND id_produto = ?";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bind_param("iii", $quantidade, $this->idCarrinho, $idProduto);
        } else {
            $sql = "INSERT INTO item_carrinho
                    (id_carrinho, id_produto, quantidade)
                    VALUES (?, ?, 1)";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bind_param("ii", $this->idCarrinho, $idProduto);
        }

        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function listar() {
        $itens = [];

        $sql = "SELECT * FROM item_carrinho WHERE id_carrinho = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("i", $this->idCarrinho);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($item = $resultado->fetch_assoc()) {
            $idProduto = (int) $item["id_produto"];

            $sqlProduto = "SELECT * FROM produtos WHERE id_produto = ?";
            $stmtProduto = $this->conexao->prepare($sqlProduto);
            $stmtProduto->bind_param("i", $idProduto);
            $stmtProduto->execute();
            $produto = $stmtProduto->get_result()->fetch_assoc();
            $stmtProduto->close();

            if ($produto) {
                $item["nome_produto"] = $produto["nome_produto"];
                $item["preco"] = $produto["preco"];
                $item["imagem"] = $produto["imagem"];
                $item["estoque"] = $produto["estoque"];
                $item["subtotal"] = (float) $produto["preco"] * (int) $item["quantidade"];
                $itens[] = $item;
            }
        }

        $stmt->close();
        return $itens;
    }

    public function alterarQuantidade($idProduto, $quantidade) {
        $idProduto = (int) $idProduto;
        $quantidade = (int) $quantidade;

        if ($quantidade < 1) {
            return false;
        }

        $sql = "SELECT estoque FROM produtos WHERE id_produto = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("i", $idProduto);
        $stmt->execute();
        $produto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$produto || $quantidade > (int) $produto["estoque"]) {
            return false;
        }

        $sql = "UPDATE item_carrinho SET quantidade = ?
                WHERE id_carrinho = ? AND id_produto = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("iii", $quantidade, $this->idCarrinho, $idProduto);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function remover($idProduto) {
        $sql = "DELETE FROM item_carrinho
                WHERE id_carrinho = ? AND id_produto = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("ii", $this->idCarrinho, $idProduto);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function finalizar($selecionados, $idEndereco) {
        $selecionados = array_values(array_unique(array_map("intval", $selecionados)));
        $idEndereco = (int) $idEndereco;

        if (count($selecionados) === 0 || $idEndereco < 1) {
            return false;
        }

        $sql = "SELECT id_endereco FROM enderecos
                WHERE id_endereco = ? AND id_usuario = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("ii", $idEndereco, $this->idUsuario);
        $stmt->execute();
        $enderecoValido = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$enderecoValido) {
            return false;
        }

        $itensCarrinho = $this->listar();
        $compra = [];
        $total = 0;

        foreach ($itensCarrinho as $item) {
            if (in_array((int) $item["id_produto"], $selecionados, true)) {
                if ((int) $item["quantidade"] < 1 ||
                    (int) $item["quantidade"] > (int) $item["estoque"]) {
                    return false;
                }

                $compra[] = $item;
                $total += (float) $item["subtotal"];
            }
        }

        if (count($compra) !== count($selecionados)) {
            return false;
        }

        $this->conexao->begin_transaction();

        try {
            $status = "compra realizada com sucesso";

            $sql = "INSERT INTO pedido
                    (id_usuario, id_endereco, data_pedido, valor_pedido, status_pedido)
                    VALUES (?, ?, CURDATE(), ?, ?)";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bind_param("iids", $this->idUsuario, $idEndereco, $total, $status);
            $stmt->execute();
            $idPedido = (int) $this->conexao->insert_id;
            $stmt->close();

            foreach ($compra as $item) {
                $idProduto = (int) $item["id_produto"];
                $quantidade = (int) $item["quantidade"];
                $preco = (float) $item["preco"];
                $subtotal = (float) $item["subtotal"];

                $sql = "UPDATE produtos
                        SET estoque = estoque - ?
                        WHERE id_produto = ? AND estoque >= ?";
                $stmt = $this->conexao->prepare($sql);
                $stmt->bind_param("iii", $quantidade, $idProduto, $quantidade);
                $stmt->execute();

                if ($stmt->affected_rows !== 1) {
                    $stmt->close();
                    throw new Exception("Estoque insuficiente.");
                }
                $stmt->close();

                $sql = "INSERT INTO item_pedido
                        (id_pedido, id_produto, quantidade, valor_pedido, preci_unit)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->conexao->prepare($sql);
                $stmt->bind_param("iiidd", $idPedido, $idProduto, $quantidade, $subtotal, $preco);
                $stmt->execute();
                $stmt->close();

                $this->remover($idProduto);
            }

            $this->conexao->commit();
            return $idPedido;

        } catch (Throwable $erro) {
            $this->conexao->rollback();
            return false;
        }
    }
}
?>
