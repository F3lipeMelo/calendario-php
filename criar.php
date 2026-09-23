<?php

require "conexao.php";


// ================================
// DATA RECEBIDA PELO CALENDÁRIO
// ================================

$dataSelecionada = isset($_GET["data"])
    ? $_GET["data"]
    : "";


// ================================
// MÊS E ANO PARA RETORNAR
// ================================

$mesRetorno = isset($_GET["mes"])
    ? (int) $_GET["mes"]
    : (int) date("n");

$anoRetorno = isset($_GET["ano"])
    ? (int) $_GET["ano"]
    : (int) date("Y");


// ================================
// FORMULÁRIO ENVIADO
// ================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // Pegando dados do formulário
    $titulo = trim($_POST["titulo"]);

    $descricao = trim($_POST["descricao"]);

    $data = $_POST["data"];

    $hora = $_POST["hora"];


    // Mês para retornar depois do cadastro
    $mesRetorno = (int) $_POST["mes"];

    $anoRetorno = (int) $_POST["ano"];


    // ================================
    // VALIDAÇÃO
    // ================================

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
    // INSERT
    // ================================

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

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <link rel="stylesheet" href="css/style.css">

    <title>Novo compromisso</title>

</head>

<body>


    <h1>Novo compromisso</h1>


    <form method="POST">


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
        ></textarea>


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
            value="<?php echo htmlspecialchars($dataSelecionada); ?>"
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
            required
        >


        <br><br>


        <!-- ================================
             CADASTRAR
        ================================= -->

        <button type="submit">
            Cadastrar
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