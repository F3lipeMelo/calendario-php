<?php

require "conexao.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    die("Método inválido.");

}

$id = $_POST["id"];

$sql = "
    DELETE FROM compromissos
    WHERE id = :id
";

$stmt = $conexao->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

header("Location: index.php");
exit;