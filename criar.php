<?php

require "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($titulo) || empty($data) || empty($hora)) {

        die("Título, data e horário são obrigatórios.");

    }

    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $data = $_POST["data"];
    $hora = $_POST["hora"];

    $sql = "
        INSERT INTO compromissos
        (
            titulo,
            descricao,
            data_compromisso,
            hora_compromisso
        )
        VALUES
        (
            :titulo,
            :descricao,
            :data,
            :hora
        )
    ";

    $stmt = $conexao->prepare($sql);

    $stmt->execute([
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":data" => $data,
        ":hora" => $hora
    ]);

    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Novo compromisso</title>
</head>

<body>

    <h1>Novo compromisso</h1>

    <form method="POST">

        <label for="titulo">Título:</label>

        <br>

        <input
            type="text"
            id="titulo"
            name="titulo"
            required
        >

        <br><br>

        <label for="descricao">Descrição:</label>

        <br>

        <textarea
            id="descricao"
            name="descricao"
        ></textarea>

        <br><br>

        <label for="data">Data:</label>

        <br>

        <input
            type="date"
            id="data"
            name="data"
            required
        >

        <br><br>

        <label for="hora">Horário:</label>

        <br>

        <input
            type="time"
            id="hora"
            name="hora"
            required
        >

        <br><br>

        <button type="submit">
            Cadastrar
        </button>

    </form>

    <br>

    <a href="index.php">Voltar</a>

</body>

</html>