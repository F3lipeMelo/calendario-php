<?php

require "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST["id"];
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $data = $_POST["data"];
    $hora = $_POST["hora"];

    if (empty($titulo) || empty($data) || empty($hora)) {

        die("Título, data e horário são obrigatórios.");

    }

    $sql = "
        UPDATE compromissos
        SET
            titulo = :titulo,
            descricao = :descricao,
            data_compromisso = :data,
            hora_compromisso = :hora
        WHERE id = :id
    ";

    $stmt = $conexao->prepare($sql);

    $stmt->execute([
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":data" => $data,
        ":hora" => $hora,
        ":id" => $id
    ]);

    header("Location: index.php");
    exit;

}

$id = $_GET["id"];

$sql = "
    SELECT *
    FROM compromissos
    WHERE id = :id
";

$stmt = $conexao->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$compromisso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compromisso) {

    die("Compromisso não encontrado.");

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar compromisso</title>
</head>

<body>

    <h1>Editar compromisso</h1>

    <form method="POST">

        <input
            type="hidden"
            name="id"
            value="<?php echo $compromisso["id"]; ?>"
        >

        <label for="titulo">Título:</label>
        <br>

        <input
            type="text"
            id="titulo"
            name="titulo"
            value="<?php echo htmlspecialchars($compromisso["titulo"]); ?>"
            required
        >

        <br><br>

        <label for="descricao">Descrição:</label>
        <br>

        <textarea
            id="descricao"
            name="descricao"
        ><?php echo $compromisso["descricao"]; ?></textarea>

        <br><br>

        <label for="data">Data:</label>
        <br>

        <input
            type="date"
            id="data"
            name="data"
            value="<?php echo $compromisso["data_compromisso"]; ?>"
            required
        >

        <br><br>

        <label for="hora">Horário:</label>
        <br>

        <input
            type="time"
            id="hora"
            name="hora"
            value="<?php echo $compromisso["hora_compromisso"]; ?>"
            required
        >

        <br><br>

        <button type="submit">
            Salvar alterações
        </button>

    </form>

    <br>

    <a href="index.php">Cancelar</a>

</body>

</html>