<?php

require "config.php";

try {

    $conexao = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

} catch (PDOException $erro) {

    die("Erro ao conectar: " . $erro->getMessage());

}