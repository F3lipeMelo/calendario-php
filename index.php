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

    <?php if (empty($compromissos)): ?>

        <p>
            Nenhum compromisso cadastrado.
        </p>

    <?php else: ?>

        <?php foreach ($compromissos as $compromisso): ?>

            <h2>
                <?php echo htmlspecialchars($compromisso["titulo"]); ?>
            </h2>

            <p>
                <?php echo htmlspecialchars($compromisso["descricao"]); ?>
            </p>

            <p>

                <?php
                    echo date(
                        "d/m/Y",
                        strtotime($compromisso["data_compromisso"])
                    );
                ?>

                às

                <?php
                    echo date(
                        "H:i",
                        strtotime($compromisso["hora_compromisso"])
                    );
                ?>

            </p>

            <form
                action="alterar_status.php"
                method="POST"
                style="display:inline;"
            >

                <p>
                    Status:

                    <?php if ($compromisso["concluido"]): ?>

                        Concluído

                    <?php else: ?>

                        Pendente

                    <?php endif; ?>
                </p>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $compromisso["id"]; ?>"
                >

                <button type="submit">

                    <?php if ($compromisso["concluido"]): ?>

                        Marcar como pendente

                    <?php else: ?>

                        Marcar como concluído

                    <?php endif; ?>

                </button>

            </form>

            <a href="editar.php?id=<?php echo $compromisso["id"]; ?>">
                Editar
            </a>

            <form
                action="excluir.php"
                method="POST"
                style="display:inline;"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $compromisso["id"]; ?>"
                >

                <button
                    type="submit"
                    onclick="return confirm('Deseja realmente excluir este compromisso?')"
                >
                    Excluir
                </button>

            </form>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>

</body>

</html>