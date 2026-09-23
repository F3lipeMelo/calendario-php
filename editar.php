<?php

require "conexao.php";


// ================================
// SALVAR ALTERAÇÕES
// ================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // ================================
    // DADOS RECEBIDOS
    // ================================

    $id = (int) $_POST["id"];

    $titulo = trim(
        $_POST["titulo"]
    );

    $descricao = trim(
        $_POST["descricao"]
    );

    $data = $_POST["data"];

    $hora = $_POST["hora"];


    $mesRetorno =
        (int) $_POST["mes"];

    $anoRetorno =
        (int) $_POST["ano"];


    // ================================
    // VALIDAÇÃO
    // ================================

    if ($id <= 0) {

        die(
            "Compromisso inválido."
        );

    }


    if (
        empty($titulo) ||
        empty($data) ||
        empty($hora)
    ) {

        die(
            "Título, data e horário são obrigatórios."
        );

    }


    // ================================
    // UPDATE
    // ================================

    $sql = "
        UPDATE compromissos
        SET
            titulo = :titulo,
            descricao = :descricao,
            data_compromisso = :data,
            hora_compromisso = :hora
        WHERE id = :id
    ";


    $stmt =
        $conexao->prepare($sql);


    $stmt->execute([

        ":titulo" => $titulo,

        ":descricao" => $descricao,

        ":data" => $data,

        ":hora" => $hora,

        ":id" => $id

    ]);


    // ================================
    // VOLTAR PARA O CALENDÁRIO
    // ================================

    header(
        "Location: index.php?mes="
        . $mesRetorno
        . "&ano="
        . $anoRetorno
    );

    exit;

}


// ================================
// CARREGAR COMPROMISSO
// ================================

if (!isset($_GET["id"])) {

    die(
        "ID do compromisso não informado."
    );

}


$id = (int) $_GET["id"];


if ($id <= 0) {

    die(
        "Compromisso inválido."
    );

}


// ================================
// MÊS E ANO DE RETORNO
// ================================

$mesRetorno = isset($_GET["mes"])
    ? (int) $_GET["mes"]
    : (int) date("n");


$anoRetorno = isset($_GET["ano"])
    ? (int) $_GET["ano"]
    : (int) date("Y");


// ================================
// SELECT
// ================================

$sql = "
    SELECT *
    FROM compromissos
    WHERE id = :id
";


$stmt =
    $conexao->prepare($sql);


$stmt->execute([
    ":id" => $id
]);


$compromisso =
    $stmt->fetch(
        PDO::FETCH_ASSOC
    );


// ================================
// VERIFICAR SE EXISTE
// ================================

if (!$compromisso) {

    die(
        "Compromisso não encontrado."
    );

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <link rel="stylesheet" href="css/style.css">

    <title>
        Editar compromisso
    </title>

</head>

<body>


    <h1>
        Editar compromisso
    </h1>


    <form method="POST">


        <!-- ================================
             ID
        ================================= -->

        <input
            type="hidden"
            name="id"
            value="<?php echo $compromisso["id"]; ?>"
        >


        <!-- ================================
             MÊS E ANO DE RETORNO
        ================================= -->

        <input
            type="hidden"
            name="mes"
            value="<?php echo $mesRetorno; ?>"
        >


        <input
            type="hidden"
            name="ano"
            value="<?php echo $anoRetorno; ?>"
        >


        <!-- ================================
             TÍTULO
        ================================= -->

        <label for="titulo">
            Título:
        </label>

        <br>


        <input
            type="text"
            id="titulo"
            name="titulo"
            value="<?php
                echo htmlspecialchars(
                    $compromisso["titulo"]
                );
            ?>"
            required
        >


        <br><br>


        <!-- ================================
             DESCRIÇÃO
        ================================= -->

        <label for="descricao">
            Descrição:
        </label>

        <br>


        <textarea
            id="descricao"
            name="descricao"
        ><?php
            echo htmlspecialchars(
                $compromisso["descricao"]
            );
        ?></textarea>


        <br><br>


        <!-- ================================
             DATA
        ================================= -->

        <label for="data">
            Data:
        </label>

        <br>


        <input
            type="date"
            id="data"
            name="data"
            value="<?php
                echo $compromisso[
                    "data_compromisso"
                ];
            ?>"
            required
        >


        <br><br>


        <!-- ================================
             HORÁRIO
        ================================= -->

        <label for="hora">
            Horário:
        </label>

        <br>


        <input
            type="time"
            id="hora"
            name="hora"
            value="<?php
                echo substr(
                    $compromisso[
                        "hora_compromisso"
                    ],
                    0,
                    5
                );
            ?>"
            required
        >


        <br><br>


        <!-- ================================
             SALVAR
        ================================= -->

        <button type="submit">
            Salvar alterações
        </button>


    </form>


    <br>


    <a
        href="index.php?mes=<?php echo $mesRetorno; ?>&ano=<?php echo $anoRetorno; ?>"
    >
        Cancelar
    </a>


</body>

</html>