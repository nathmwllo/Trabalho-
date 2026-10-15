<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location:../usuarios/login.php");
    exit;
}

if ($_SESSION["cargo"] != "Administrador") {
    header("Location:../index.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: listagem.php");
    exit;
}

$id = (int) $_GET["id"];

$sql = "DELETE FROM produtos WHERE id_produto = ?";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: listagem.php");
exit;

?>
