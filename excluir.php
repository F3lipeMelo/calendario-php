<?php
require "conexao.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") die("Método inválido.");
$id=(int)($_POST["id"]??0);$mes=(int)($_POST["mes"]??date("n"));$ano=(int)($_POST["ano"]??date("Y"));
if($id<=0) die("Compromisso inválido.");
$stmt=$conexao->prepare("DELETE FROM compromissos WHERE id = :id");$stmt->execute([":id"=>$id]);
header("Location: index.php?mes=$mes&ano=$ano");exit;
