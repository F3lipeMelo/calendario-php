<?php

require "conexao.php";


// ================================
// MÊS E ANO QUE SERÃO EXIBIDOS
// ================================

$mes = isset($_GET["mes"])
    ? (int) $_GET["mes"]
    : (int) date("n");

$ano = isset($_GET["ano"])
    ? (int) $_GET["ano"]
    : (int) date("Y");


// Evita meses inválidos pela URL
if ($mes < 1 || $mes > 12) {

    $mes = (int) date("n");
    $ano = (int) date("Y");

}


// ================================
// NOMES DOS MESES
// ================================

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


// ================================
// INFORMAÇÕES DO MÊS
// ================================

$totalDias = cal_days_in_month(
    CAL_GREGORIAN,
    $mes,
    $ano
);


// Descobre em qual dia da semana começa o mês
$primeiroDia = mktime(
    0,
    0,
    0,
    $mes,
    1,
    $ano
);


// 1 = segunda
// 2 = terça
// ...
// 7 = domingo
$diaSemanaInicio = (int) date(
    "N",
    $primeiroDia
);


// ================================
// MÊS ANTERIOR
// ================================

$mesAnterior = $mes - 1;
$anoAnterior = $ano;

if ($mesAnterior < 1) {

    $mesAnterior = 12;
    $anoAnterior--;

}


// ================================
// PRÓXIMO MÊS
// ================================

$proximoMes = $mes + 1;
$proximoAno = $ano;

if ($proximoMes > 12) {

    $proximoMes = 1;
    $proximoAno++;

}


// ================================
// PERÍODO QUE SERÁ BUSCADO NO BANCO
// ================================

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


// ================================
// BUSCAR COMPROMISSOS DO MÊS
// ================================

$sql = "
    SELECT *
    FROM compromissos
    WHERE data_compromisso BETWEEN :inicio AND :fim
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


// ================================
// ORGANIZAR COMPROMISSOS POR DIA
// ================================

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


// ================================
// DATA DE HOJE
// ================================

$hojeDia = (int) date("j");
$hojeMes = (int) date("n");
$hojeAno = (int) date("Y");

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <link rel="stylesheet" href="css/style.css">

    <title>Meu Calendário</title>

</head>

<body>
    <div class="container">

        <header class="cabecalho">

            <div>

                <h1>Meu Calendário</h1>

                <p>
                    Organize seus compromissos e atividades pessoais.
                </p>

            </div>

            <!-- ================================
                NOVO COMPROMISSO
            ================================= -->

            <a
                class="botao botao-principal"
                href="criar.php?mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>"
            >
                + Novo compromisso
            </a>

        </header>


        <!-- ================================
            NAVEGAÇÃO ENTRE MESES
        ================================= -->

        <div class="navegacao-calendario">

            <a
                class="botao"
                href="index.php?mes=<?php echo $mesAnterior; ?>&ano=<?php echo $anoAnterior; ?>"
            >
                ← Anterior
            </a>


            <div class="mes-atual">

                <h2>
                    <?php echo $nomesMeses[$mes]; ?>
                    <?php echo $ano; ?>
                </h2>

                <a href="index.php">
                    Ir para hoje
                </a>

            </div>


            <a
                class="botao"
                href="index.php?mes=<?php echo $proximoMes; ?>&ano=<?php echo $proximoAno; ?>"
            >
                Próximo →
            </a>

        </div>


        <br><br>


        <!-- VOLTAR PARA O MÊS ATUAL -->

        <a href="index.php">
            Mês atual
        </a>


        <br><br>


        <!-- ================================
            CALENDÁRIO
        ================================= -->

        <table class="calendario">

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

                    // Cria espaços vazios antes do dia 1
                    for (
                        $i = 1;
                        $i < $diaSemanaInicio;
                        $i++
                    ) {

                        echo "<td></td>";

                    }


                    $diaSemanaAtual = $diaSemanaInicio;


                    // Cria os dias do mês
                    for (
                        $dia = 1;
                        $dia <= $totalDias;
                        $dia++
                    ) {


                        // Monta a data completa daquele dia
                        $dataDoDia = sprintf(
                            "%04d-%02d-%02d",
                            $ano,
                            $mes,
                            $dia
                        );


                        // Verifica se é hoje
                        $ehHoje =
                            $dia == $hojeDia &&
                            $mes == $hojeMes &&
                            $ano == $hojeAno;


                        // Abre a célula
                        if ($ehHoje) {

                            echo '<td class="dia hoje">';

                        } else {

                            echo '<td class="dia">';

                        }


                        // ================================
                        // NÚMERO DO DIA
                        // ================================

                        echo '<a
                            class="numero-dia"
                            href="criar.php?data='
                            . $dataDoDia
                            . '&mes='
                            . $mes
                            . '&ano='
                            . $ano
                            . '"
                        >';

                        echo $dia;
                        echo "</a>";

                        echo "<br><br>";


                        // ================================
                        // COMPROMISSOS DO DIA
                        // ================================

                        if (
                            isset(
                                $compromissosPorDia[$dia]
                            )
                        ) {

                            foreach (
                                $compromissosPorDia[$dia]
                                as $compromisso
                            ) {

                                if ($compromisso["concluido"]) {

                                    echo '<div class="compromisso concluido">';

                                } else {

                                    echo '<div class="compromisso">';

                                }

                                // Link para editar
                                echo '<a href="editar.php?id='
                                    . $compromisso["id"]
                                    . '&mes='
                                    . $mes
                                    . '&ano='
                                    . $ano
                                    . '">';


                                // Horário
                                echo date(
                                    "H:i",
                                    strtotime(
                                        $compromisso[
                                            "hora_compromisso"
                                        ]
                                    )
                                );


                                echo " - ";


                                // Título
                                echo htmlspecialchars(
                                    $compromisso["titulo"]
                                );


                                echo "</a>";

                                echo "<br>";


                                // ================================
                                // ALTERAR STATUS
                                // ================================

                                echo '
                                    <form
                                        action="alterar_status.php"
                                        method="POST"
                                        style="display:inline;"
                                    >
                                ';

                                echo '
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="'
                                        . $compromisso["id"]
                                        . '"
                                    >
                                ';

                                echo '
                                    <input
                                        type="hidden"
                                        name="mes"
                                        value="'
                                        . $mes
                                        . '"
                                    >
                                ';

                                echo '
                                    <input
                                        type="hidden"
                                        name="ano"
                                        value="'
                                        . $ano
                                        . '"
                                    >
                                ';


                                echo '<button class="botao-pequeno" type="submit">';

                                if ($compromisso["concluido"]) {

                                    echo "Pendente";

                                } else {

                                    echo "Concluir";

                                }

                                echo "</button>";

                                echo "</form>";


                                // ================================
                                // EXCLUIR
                                // ================================

                                echo '
                                    <form
                                        action="excluir.php"
                                        method="POST"
                                        style="display:inline;"
                                    >
                                ';

                                echo '
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="'
                                        . $compromisso["id"]
                                        . '"
                                    >
                                ';

                                echo '
                                    <input
                                        type="hidden"
                                        name="mes"
                                        value="'
                                        . $mes
                                        . '"
                                    >
                                ';

                                echo '
                                    <input
                                        type="hidden"
                                        name="ano"
                                        value="'
                                        . $ano
                                        . '"
                                    >
                                ';

                                echo '
                                    <button
                                        class="botao-pequeno botao-perigo"
                                        type="submit"
                                        onclick="
                                            return confirm(
                                                \'Deseja realmente excluir este compromisso?\'
                                            );
                                        "
                                    >
                                        Excluir
                                    </button>
                                ';

                                echo "</form>";


                                echo "</div>";

                                echo "<br>";

                            }

                        }


                        // Fecha a célula
                        echo "</td>";


                        // ================================
                        // MUDANÇA DE SEMANA
                        // ================================

                        if ($diaSemanaAtual == 7) {

                            if ($dia != $totalDias) {

                                echo "</tr><tr>";

                            }

                            $diaSemanaAtual = 1;

                        } else {

                            $diaSemanaAtual++;

                        }

                    }


                    // ================================
                    // ESPAÇOS VAZIOS NO FINAL DO MÊS
                    // ================================

                    if ($diaSemanaAtual != 1) {

                        for (
                            $i = $diaSemanaAtual;
                            $i <= 7;
                            $i++
                        ) {

                            echo "<td></td>";

                        }

                    }

                    ?>

                </tr>

            </tbody>

        </table>

    </div>

</body>

</html>