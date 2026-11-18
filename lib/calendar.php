<?php
declare(strict_types=1);

session_start();

const ITEM_TYPES = [
    'tarefa' => 'Tarefa',
    'compromisso' => 'Compromisso',
    'prazo' => 'Prazo',
    'lembrete' => 'Lembrete',
    'evento' => 'Evento',
    'outro' => 'Outro',
];

function escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function parse_date($value): ?DateTimeImmutable
{
    if (!is_string($value)) return null;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $date : null;
}

function calendar_url(string $view = 'month', ?DateTimeImmutable $date = null, bool $select = false): string
{
    $date ??= new DateTimeImmutable('today');
    $params = ['view' => $view, 'mes' => $date->format('n'), 'ano' => $date->format('Y')];
    if ($select || $view !== 'month') $params['data'] = $date->format('Y-m-d');
    return 'index.php?' . http_build_query($params);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Sessão expirada. Recarregue a página e tente novamente.');
    }
}

function item_url(array $context = []): string
{
    $view = in_array($context['view'] ?? '', ['month', 'week', 'day'], true) ? $context['view'] : 'month';
    $date = parse_date($context['data'] ?? '') ?? new DateTimeImmutable('today');
    return calendar_url($view, $date, true);
}

function fetch_items(PDO $db, DateTimeImmutable $start, DateTimeImmutable $end): array
{
    $stmt = $db->prepare('SELECT id, titulo, descricao, data_compromisso, data_fim, hora_compromisso, tipo, concluido
        FROM compromissos
        WHERE data_compromisso <= :end AND COALESCE(data_fim, data_compromisso) >= :start
        ORDER BY data_compromisso, hora_compromisso, id');
    $stmt->execute([':start' => $start->format('Y-m-d'), ':end' => $end->format('Y-m-d')]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function group_items_by_day(array $items, DateTimeImmutable $start, DateTimeImmutable $end): array
{
    $grouped = [];
    foreach ($items as $item) {
        $first = parse_date($item['data_compromisso']);
        $last = parse_date($item['data_fim'] ?? '') ?? $first;
        if (!$first || !$last) continue;
        if ($first < $start) $first = $start;
        if ($last > $end) $last = $end;
        for ($day = $first; $day <= $last; $day = $day->modify('+1 day')) {
            $grouped[$day->format('Y-m-d')][] = $item;
        }
    }
    foreach ($grouped as &$dayItems) {
        usort($dayItems, static fn(array $a, array $b): int =>
            strcmp((string) $a['hora_compromisso'], (string) $b['hora_compromisso'])
            ?: ((int) $a['id'] <=> (int) $b['id']));
    }
    unset($dayItems);
    return $grouped;
}

function item_range(array $item): string
{
    $from = parse_date($item['data_compromisso']);
    $to = parse_date($item['data_fim'] ?? '') ?? $from;
    if (!$from || !$to) return '';
    $label = $from->format('d/m/Y') . ' às ' . substr((string) $item['hora_compromisso'], 0, 5);
    if ($to > $from) $label .= ' até ' . $to->format('d/m/Y');
    return $label;
}

function item_actions(array $item, string $view, DateTimeImmutable $date): void
{
    $id = (int) $item['id'];
    $context = ['view' => $view, 'data' => $date->format('Y-m-d')];
    $query = http_build_query(['id' => $id] + $context);
    echo '<div class="item-actions">';
    echo '<a class="action-link" href="editar.php?' . escape($query) . '">Editar</a>';
    echo '<form method="post" action="alterar_status.php">';
    echo '<input type="hidden" name="csrf_token" value="' . escape(csrf_token()) . '">';
    echo '<input type="hidden" name="id" value="' . $id . '">';
    echo '<input type="hidden" name="view" value="' . escape($view) . '">';
    echo '<input type="hidden" name="data" value="' . escape($date->format('Y-m-d')) . '">';
    echo '<button type="submit" class="action-link">' . ($item['concluido'] ? 'Reabrir' : 'Concluir') . '</button></form>';
    echo '<form method="post" action="excluir.php" data-confirm="Excluir esta marcação? Esta ação não pode ser desfeita.">';
    echo '<input type="hidden" name="csrf_token" value="' . escape(csrf_token()) . '">';
    echo '<input type="hidden" name="id" value="' . $id . '">';
    echo '<input type="hidden" name="view" value="' . escape($view) . '">';
    echo '<input type="hidden" name="data" value="' . escape($date->format('Y-m-d')) . '">';
    echo '<button type="submit" class="action-link danger">Excluir</button></form>';
    echo '</div>';
}
