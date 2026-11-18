<?php
declare(strict_types=1);
require __DIR__ . '/conexao.php';
require __DIR__ . '/lib/calendar.php';

$editing = ($formMode ?? '') === 'edit';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$view = in_array($_POST['view'] ?? $_GET['view'] ?? 'month', ['month', 'week', 'day'], true)
    ? ($_POST['view'] ?? $_GET['view'] ?? 'month') : 'month';
$contextDate = parse_date($_POST['context_date'] ?? $_GET['data'] ?? '') ?? new DateTimeImmutable('today');
$existing = null;
if ($editing) {
    if ($id <= 0) { http_response_code(400); exit('Marcação inválida.'); }
    $stmt = $conexao->prepare('SELECT * FROM compromissos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) { http_response_code(404); exit('Marcação não encontrada.'); }
}
$values = [
    'titulo' => $existing['titulo'] ?? '',
    'descricao' => $existing['descricao'] ?? '',
    'data_compromisso' => $existing['data_compromisso'] ?? $contextDate->format('Y-m-d'),
    'data_fim' => $existing['data_fim'] ?? '',
    'hora_compromisso' => isset($existing['hora_compromisso']) ? substr($existing['hora_compromisso'], 0, 5) : '09:00',
    'tipo' => $existing['tipo'] ?? 'compromisso',
];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_) $values[$key] = trim((string) ($_POST[$key] ?? ''));
    $start = parse_date($values['data_compromisso']);
    $finish = $values['data_fim'] === '' ? $start : parse_date($values['data_fim']);
    if ($values['titulo'] === '') $errors[] = 'Informe um título.';
    if (strlen($values['titulo']) > 150) $errors[] = 'O título deve ter até 150 caracteres.';
    if (strlen($values['descricao']) > 5000) $errors[] = 'A descrição é longa demais.';
    if (!$start || !$finish) $errors[] = 'Informe datas válidas.';
    if ($start && $finish && $finish < $start) $errors[] = 'A data final deve ser igual ou posterior à inicial.';
    if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $values['hora_compromisso'])) $errors[] = 'Informe um horário válido.';
    if (!array_key_exists($values['tipo'], ITEM_TYPES)) $errors[] = 'Escolha um tipo válido.';
    if (!$errors) {
        $params = [
            ':titulo' => $values['titulo'],
            ':descricao' => $values['descricao'],
            ':inicio' => $start->format('Y-m-d'),
            ':fim' => $finish > $start ? $finish->format('Y-m-d') : null,
            ':hora' => $values['hora_compromisso'],
            ':tipo' => $values['tipo'],
        ];
        if ($editing) {
            $params[':id'] = $id;
            $stmt = $conexao->prepare('UPDATE compromissos SET titulo=:titulo, descricao=:descricao,
                data_compromisso=:inicio, data_fim=:fim, hora_compromisso=:hora, tipo=:tipo WHERE id=:id');
        } else {
            $stmt = $conexao->prepare('INSERT INTO compromissos
                (titulo, descricao, data_compromisso, data_fim, hora_compromisso, tipo)
                VALUES (:titulo, :descricao, :inicio, :fim, :hora, :tipo)');
        }
        $stmt->execute($params);
        header('Location: ' . calendar_url($view, $start, true));
        exit;
    }
}
$heading = $editing ? 'Editar marcação' : 'Nova marcação';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape($heading) ?> · Hoshi Calendar</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="form-page">
<main class="formulario-container">
    <a class="back-link" href="<?= escape(calendar_url($view, $contextDate, true)) ?>">← Voltar ao calendário</a>
    <p class="eyebrow">HOSHI CALENDAR</p>
    <h1><?= escape($heading) ?></h1>
    <p class="muted">Escolha o tipo e o período. Uma marcação pode durar um dia ou atravessar semanas.</p>
    <?php if ($errors): ?>
        <div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= escape($error) ?></p><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="view" value="<?= escape($view) ?>">
        <input type="hidden" name="context_date" value="<?= escape($contextDate->format('Y-m-d')) ?>">
        <div class="campo"><label for="titulo">Título</label><input id="titulo" name="titulo" maxlength="150" required autofocus value="<?= escape($values['titulo']) ?>" placeholder="Ex.: Semana de provas"></div>
        <div class="campo"><label for="tipo">Tipo</label><select id="tipo" name="tipo"><?php foreach (ITEM_TYPES as $key => $label): ?><option value="<?= escape($key) ?>" <?= $values['tipo'] === $key ? 'selected' : '' ?>><?= escape($label) ?></option><?php endforeach; ?></select></div>
        <div class="form-row">
            <div class="campo"><label for="data_compromisso">Data inicial</label><input type="date" id="data_compromisso" name="data_compromisso" required value="<?= escape($values['data_compromisso']) ?>"></div>
            <div class="campo"><label for="data_fim">Data final <small>opcional</small></label><input type="date" id="data_fim" name="data_fim" value="<?= escape($values['data_fim']) ?>"></div>
        </div>
        <div class="campo"><label for="hora_compromisso">Horário de início</label><input type="time" id="hora_compromisso" name="hora_compromisso" required value="<?= escape($values['hora_compromisso']) ?>"></div>
        <div class="campo"><label for="descricao">Descrição <small>opcional</small></label><textarea id="descricao" name="descricao" maxlength="5000" placeholder="Detalhes que ajudam você a se organizar"><?= escape($values['descricao']) ?></textarea></div>
        <div class="acoes-formulario"><button class="btn primary" type="submit"><?= $editing ? 'Salvar alterações' : 'Criar marcação' ?></button><a class="btn ghost" href="<?= escape(calendar_url($view, $contextDate, true)) ?>">Cancelar</a></div>
    </form>
</main>
<script src="js/app.js"></script>
</body></html>
