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

    <div class="formulario-container">

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

            <div class="campo">

                <label for="titulo">
                    Título
                </label>

                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    required
                >

            </div>


            <!-- ================================
                DESCRIÇÃO
            ================================= -->

            <div class="campo">

                <label for="descricao">
                    Descrição
                </label>

                <textarea
                    id="descricao"
                    name="descricao"
                ></textarea>

            </div>


            <!-- ================================
                DATA
            ================================= -->

            <div class="campo">

                <label for="data">
                    Data
                </label>

                <input
                    type="date"
                    id="data"
                    name="data"
                    value="<?php echo htmlspecialchars($dataSelecionada); ?>"
                    required
                >

            </div>


            <!-- ================================
                HORÁRIO
            ================================= -->

            <div class="campo">

                <label for="hora">
                    Horário
                </label>

                <input
                    type="time"
                    id="hora"
                    name="hora"
                    required
                >

            </div>


            <!-- ================================
                CADASTRAR
            ================================= -->

            <div class="acoes-formulario">

                <button
                    class="botao botao-principal"
                    type="submit"
                >
                    Cadastrar
                </button>

                <a
                    class="botao"
                    href="index.php?mes=<?php echo $mesRetorno; ?>&ano=<?php echo $anoRetorno; ?>"
                >
                    Cancelar
                </a>

            </div>


        </form>


        <br>


        <a
            href="index.php?mes=<?php echo $mesRetorno; ?>&ano=<?php echo $anoRetorno; ?>"
        >
            Cancelar
        </a>

    </div>

</body>

</html>