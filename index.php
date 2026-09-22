<?php

require "conexao.php";

$mes = isset($_GET["mes"])
    ? (int) $_GET["mes"]
    : (int) date("n");

$ano = isset($_GET["ano"])
    ? (int) $_GET["ano"]
    : (int) date("Y");

$nomesMeses = [
    1 => "Janeiro",
    2 => "Fevereiro",
    3 => "Março",
    4 => "Abril",
    5 => "Maio",
    6 => "Junho",
    7 => "Julho",
    8 => "Agosto",
    9 => "Setembro",
    10 => "Outubro",
    11 => "Novembro",
    12 => "Dezembro"
];

$totalDias = cal_days_in_month(
    CAL_GREGORIAN,
    $mes,
    $ano
);

$primeiroDia = mktime(
    0,
    0,
    0,
    $mes,
    1,
    $ano
);

$diaSemanaInicio = date(
    "N",
    $primeiroDia
);

$mesAnterior = $mes - 1;
$anoAnterior = $ano;

if ($mesAnterior < 1) {

    $mesAnterior = 12;
    $anoAnterior--;

}

$proximoMes = $mes + 1;
$proximoAno = $ano;

if ($proximoMes > 12) {

    $proximoMes = 1;
    $proximoAno++;

}

$primeiraData = sprintf(
    "%04d-%02d-01",
    $ano,
    $mes
);

$ultimaData = sprintf(
    "%04d-%02d-%02d",
    $ano,
    $mes,
    $totalDias
);

$sql = "
    SELECT *
    FROM compromissos
    WHERE data_compromisso
        BETWEEN :inicio AND :fim
    ORDER BY data_compromisso, hora_compromisso
";

$stmt = $conexao->prepare($sql);

$stmt->execute([
    ":inicio" => $primeiraData,
    ":fim" => $ultimaData
]);

$compromissos = $stmt->fetchAll(
    PDO::FETCH_ASSOC
);

$compromissosPorDia = [];

foreach ($compromissos as $compromisso) {

    $dia = (int) date(
        "j",
        strtotime(
            $compromisso["data_compromisso"]
        )
    );

    $compromissosPorDia[$dia][] = $compromisso;

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>
        Meu Calendário
    </title>

</head>

<body>

    <a
        href="index.php?mes=<?php echo $mesAnterior; ?>&ano=<?php echo $anoAnterior; ?>"
    >
        ← Mês anterior
    </a>

    <h1>

        <?php echo $nomesMeses[$mes]; ?>

        <?php echo $ano; ?>

    </h1>

    <a
        href="index.php?mes=<?php echo $proximoMes; ?>&ano=<?php echo $proximoAno; ?>"
    >
        Próximo mês →
    </a>

    <br><br>

    <a href="criar.php">
        Novo compromisso
    </a>

    <br><br>

    <table border="1">

        <thead>

            <tr>
                <th>Seg</th>
                <th>Ter</th>
                <th>Qua</th>
                <th>Qui</th>
                <th>Sex</th>
                <th>Sáb</th>
                <th>Dom</th>
            </tr>

        </thead>

        <tbody>

            <tr>

                <?php

                for (
                    $i = 1;
                    $i < $diaSemanaInicio;
                    $i++
                ) {

                    echo "<td></td>";

                }

                $diaSemanaAtual =
                    $diaSemanaInicio;

                for (
                    $dia = 1;
                    $dia <= $totalDias;
                    $dia++
                ) {

                    echo "<td>";

                    echo "<strong>";
                    echo $dia;
                    echo "</strong>";

                    echo "<br>";

                    if (
                        isset(
                            $compromissosPorDia[$dia]
                        )
                    ) {

                        foreach (
                            $compromissosPorDia[$dia]
                            as $compromisso
                        ) {

                            echo '<a href="editar.php?id='
                                . $compromisso["id"]
                                . '">';

                            echo date(
                                "H:i",
                                strtotime(
                                    $compromisso[
                                        "hora_compromisso"
                                    ]
                                )
                            );

                            echo " - ";

                            echo htmlspecialchars(
                                $compromisso[
                                    "titulo"
                                ]
                            );

                            echo "</a>";

                            echo "<br>";

                        }

                    }

                    echo "</td>";

                    if (
                        $diaSemanaAtual == 7
                    ) {

                        echo "</tr><tr>";

                        $diaSemanaAtual = 1;

                    } else {

                        $diaSemanaAtual++;

                    }

                }

                ?>

            </tr>

        </tbody>

    </table>

</body>

</html>