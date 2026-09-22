<?php

require "conexao.php";

$sql = "SELECT * FROM compromissos ORDER BY data_compromisso, hora_compromisso";

$stmt = $conexao->query($sql);

$compromissos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meu Calendário</title>
</head>

<body>

    <h1>Meu Calendário</h1>

    <a href="criar.php">Novo compromisso</a>

    <hr>

    <?php foreach ($compromissos as $compromisso): ?>

        <h2>
            <?php echo $compromisso["titulo"]; ?>
        </h2>

        <p>
            <?php echo $compromisso["descricao"]; ?>
        </p>

        <p>
            Data:
            <?php echo $compromisso["data_compromisso"]; ?>
        </p>

        <p>
            Horário:
            <?php echo $compromisso["hora_compromisso"]; ?>
        </p>

        <hr>

    <?php endforeach; ?>

</body>

</html>