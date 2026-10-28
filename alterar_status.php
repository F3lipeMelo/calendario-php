<?php
require "conexao.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") die("Método inválido.");
$id=(int)($_POST["id"]??0);$mes=(int)($_POST["mes"]??date("n"));$ano=(int)($_POST["ano"]??date("Y"));$view=$_POST["view"]??"month";$data=$_POST["data"]??"";
if($id<=0) die("Compromisso inválido.");
$stmt=$conexao->prepare("UPDATE compromissos SET concluido = NOT concluido WHERE id = :id");$stmt->execute([":id"=>$id]);
$params=["view"=>$view,"mes"=>$mes,"ano"=>$ano];if($data)$params["data"]=$data;header("Location: index.php?".http_build_query($params));exit;
