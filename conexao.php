<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "hq_mania";

$conexao = new mysqli(
    $servidor,
    $usuario,
    $senha,
    $banco
);

if ($conexao->connect_error) {
    die("Erro ao conectar ao banco de dados.");
}

$conexao->set_charset("utf8");

?>