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

$id = $_GET["id"];

if ($id == $_SESSION["id_usuario"]) {
    die("Você não pode excluir o próprio usuário.");

}

$sql = "DELETE FROM usuarios WHERE id_usuario = ?";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: usuarios.php");
exit;

?>