<?php
declare(strict_types=1);
require __DIR__ . '/conexao.php';
require __DIR__ . '/lib/calendar.php';

$view = in_array($_GET['view'] ?? '', ['month', 'week', 'day'], true) ? $_GET['view'] : 'month';
$today = new DateTimeImmutable('today');
$selected = parse_date($_GET['data'] ?? '') ?? $today;
$year = (int) ($_GET['ano'] ?? $selected->format('Y'));
$year = max(1900, min(2100, $year));
$month = (int) ($_GET['mes'] ?? $selected->format('n'));
$month = max(1, min(12, $month));
$monthDate = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
$monthNames = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
$weekDays = ['Seg','Ter','Qua','Qui','Sex','Sáb','Dom'];
$typeFilter = array_key_exists($_GET['tipo'] ?? '', ITEM_TYPES) ? $_GET['tipo'] : '';
if ($view === 'day') {
    $start = $selected; $end = $selected;
    $title = $selected->format('d') . ' de ' . $monthNames[(int)$selected->format('n')] . ' de ' . $selected->format('Y');
    $previous = $selected->modify('-1 day'); $next = $selected->modify('+1 day');
} elseif ($view === 'week') {
    $start = $selected->modify('monday this week'); $end = $start->modify('+6 days');
    $title = $start->format('d/m') . ' a ' . $end->format('d/m/Y');
    $previous = $start->modify('-7 days'); $next = $start->modify('+7 days');
} else {
    $start = $monthDate; $end = $monthDate->modify('last day of this month');
    $title = $monthNames[$month] . ' ' . $year;
    $previous = $monthDate->modify('-1 month'); $next = $monthDate->modify('+1 month');
}
$items = fetch_items($conexao, $start, $end);
if ($typeFilter !== '') $items = array_values(array_filter($items, fn($item) => $item['tipo'] === $typeFilter));
$byDate = group_items_by_day($items, $start, $end);
$selectedKey = $selected->format('Y-m-d');
$panelOpen = $view === 'month' && isset($_GET['data']) && $selected >= $start && $selected <= $end;
$selectedItems = $byDate[$selectedKey] ?? [];
$nav = fn(DateTimeImmutable $date, string $v, bool $select=false) => calendar_url($v, $date, $select) . ($typeFilter !== '' ? '&tipo=' . rawurlencode($typeFilter) : '');
$createUrl = 'criar.php?' . http_build_query(['view'=>$view, 'data'=>$selectedKey]);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#15112c">
<title><?= escape($title) ?> · Hoshi Calendar</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="ambient a1"></div><div class="ambient a2"></div>
<main class="app">
<header class="topbar">
<a class="brand" href="index.php" aria-label="Hoshi Calendar início"><span class="brand-mark">✦</span><span><b>Hoshi</b><small>calendar</small></span></a>
<div class="top-actions"><span class="today-chip">● <?= $today->format('d/m/Y') ?></span><a class="btn primary" href="<?= escape($createUrl) ?>">＋ Nova marcação</a></div>
</header>
<section class="hero"><div><p class="eyebrow">SEU TEMPO, SEU RITMO</p><h1><?= escape($title) ?></h1><p class="muted">Tarefas, compromissos e períodos em um só lugar.</p></div>
<nav class="views" aria-label="Visão do calendário">
<a class="<?= $view==='day'?'active':'' ?>" href="<?= escape($nav($selected,'day')) ?>">Dia</a>
<a class="<?= $view==='week'?'active':'' ?>" href="<?= escape($nav($selected,'week')) ?>">Semana</a>
<a class="<?= $view==='month'?'active':'' ?>" href="<?= escape($nav($monthDate,'month')) ?>">Mês</a>
</nav></section>
<section class="toolbar card">
<div class="nav"><a class="icon" aria-label="Período anterior" href="<?= escape($nav($previous,$view)) ?>">‹</a><a class="btn ghost" href="<?= escape($nav($today,$view)) ?>">Hoje</a><a class="icon" aria-label="Próximo período" href="<?= escape($nav($next,$view)) ?>">›</a></div>
<form class="filter-form" method="get" action="index.php">
<input type="hidden" name="view" value="<?= escape($view) ?>">
<input type="hidden" name="mes" value="<?= $month ?>">
<input type="hidden" name="ano" value="<?= $year ?>">
<?php if($view!=='month' || $panelOpen): ?><input type="hidden" name="data" value="<?= escape($selectedKey) ?>"><?php endif; ?>
<label for="tipo">Filtrar tipo</label><select id="tipo" name="tipo"><option value="">Todos</option><?php foreach(ITEM_TYPES as $key=>$label): ?><option value="<?= escape($key) ?>" <?= $typeFilter===$key?'selected':'' ?>><?= escape($label) ?></option><?php endforeach; ?></select><button type="submit">Aplicar</button>
</form>
<span class="count-badge"><?= count($items) ?> <?= count($items)===1?'marcação':'marcações' ?></span>
</section>
<?php if($view==='month'): ?>
<section class="month-layout">
<div class="calendar card">
<div class="month-head"><?php foreach($weekDays as $day): ?><span><?= $day ?></span><?php endforeach; ?></div>
<div class="grid">
<?php for($i=1;$i<(int)$start->format('N');$i++): ?><div class="cell empty" aria-hidden="true"></div><?php endfor; ?>
<?php for($day=1;$day<=(int)$end->format('d');$day++):
$date=$start->setDate($year,$month,$day);$key=$date->format('Y-m-d');$events=$byDate[$key]??[];
?>
<a class="cell <?= $key===$today->format('Y-m-d')?'today':'' ?> <?= $panelOpen&&$key===$selectedKey?'selected':'' ?>" href="<?= escape($nav($date,'month',true)) ?>" aria-label="<?= $date->format('d/m/Y') ?>, <?= count($events) ?> marcações">
<b><?= $day ?></b>
<?php if($events): ?><span class="dots" aria-hidden="true"><?php foreach(array_slice($events,0,4) as $event): ?><i class="dot type-<?= escape($event['tipo']) ?> <?= $event['concluido']?'complete':'' ?>"></i><?php endforeach; ?><?php if(count($events)>4): ?><em>+<?= count($events)-4 ?></em><?php endif; ?></span><?php endif; ?>
<small><?= count($events)?count($events).' '.(count($events)===1?'item':'itens'):'Livre' ?></small>
</a>
<?php endfor; ?>
<?php for($i=(int)$end->format('N');$i<7;$i++): ?><div class="cell empty" aria-hidden="true"></div><?php endfor; ?>
</div></div>
<aside class="panel card" aria-label="Detalhes do dia">
<div class="panel-head"><div><p class="eyebrow">DETALHES DO DIA</p><h2><?= $panelOpen?$selected->format('d/m/Y'):'Escolha um dia' ?></h2></div><?php if($panelOpen): ?><a class="close" aria-label="Fechar detalhes" href="<?= escape($nav($monthDate,'month')) ?>">×</a><?php endif; ?></div>
<?php if($panelOpen): ?>
<p class="muted"><?= count($selectedItems) ?> <?= count($selectedItems)===1?'marcação':'marcações' ?> neste dia</p>
<?php foreach($selectedItems as $event): ?>
<article class="event <?= $event['concluido']?'done':'' ?> type-border-<?= escape($event['tipo']) ?>">
<div class="event-main"><div class="event-top"><span class="type-badge type-<?= escape($event['tipo']) ?>"><?= escape(ITEM_TYPES[$event['tipo']]??'Outro') ?></span><time><?= escape(substr($event['hora_compromisso'],0,5)) ?></time></div><h3><?= escape($event['titulo']) ?></h3><p class="event-range"><?= escape(item_range($event)) ?></p><?php if($event['descricao']): ?><p><?= nl2br(escape($event['descricao'])) ?></p><?php endif; ?><?php item_actions($event,'month',$selected); ?></div>
</article>
<?php endforeach; ?>
<a class="btn primary full" href="<?= escape($createUrl) ?>">＋ Adicionar neste dia</a>
<?php else: ?><div class="empty-panel"><span>✦</span><p>Clique em um dia para ver, concluir, reabrir ou excluir suas marcações.</p></div><?php endif; ?>
</aside></section>
<?php elseif($view==='week'): ?>
<section class="week-board card"><div class="week-columns">
<?php for($i=0;$i<7;$i++):$date=$start->modify("+$i days");$key=$date->format('Y-m-d'); ?>
<div class="week-col <?= $key===$today->format('Y-m-d')?'today':'' ?>">
<a class="week-col-head" href="<?= escape($nav($date,'day')) ?>"><span><?= $weekDays[$i] ?></span><b><?= $date->format('d') ?></b></a>
<?php foreach($byDate[$key]??[] as $event): ?><article class="week-event <?= $event['concluido']?'done':'' ?> type-border-<?= escape($event['tipo']) ?>">
<span class="type-badge type-<?= escape($event['tipo']) ?>"><?= escape(ITEM_TYPES[$event['tipo']]??'Outro') ?></span>
<time><?= escape(substr($event['hora_compromisso'],0,5)) ?></time><strong><?= escape($event['titulo']) ?></strong>
<small><?= escape(item_range($event)) ?></small>
<?php item_actions($event,'week',$date); ?>
</article><?php endforeach; ?>
<a class="quick" href="criar.php?view=week&data=<?= $key ?>">＋ adicionar</a>
</div><?php endfor; ?>
</div></section>
<?php else: ?>
<section class="day-board card"><div class="day-title"><div><p class="eyebrow">AGENDA DO DIA</p><h2><?= $selected->format('d/m/Y') ?></h2></div><a class="btn primary" href="<?= escape($createUrl) ?>">＋ Adicionar</a></div>
<?php if(!$selectedItems): ?><div class="empty-panel"><span>☾</span><h3>Dia livre por aqui</h3><p>Você não tem marcações cadastradas para esta data.</p></div>
<?php else: ?><div class="timeline"><?php foreach($selectedItems as $event): ?>
<article class="timeline-item <?= $event['concluido']?'done':'' ?>"><time><?= escape(substr($event['hora_compromisso'],0,5)) ?></time>
<div class="event type-border-<?= escape($event['tipo']) ?>"><div class="event-main"><span class="type-badge type-<?= escape($event['tipo']) ?>"><?= escape(ITEM_TYPES[$event['tipo']]??'Outro') ?></span><h3><?= escape($event['titulo']) ?></h3><p class="event-range"><?= escape(item_range($event)) ?></p><?php if($event['descricao']): ?><p><?= nl2br(escape($event['descricao'])) ?></p><?php endif; ?><?php item_actions($event,'day',$selected); ?></div></div>
</article>
<?php endforeach; ?></div><?php endif; ?>
</section>
<?php endif; ?>
<footer><span>✦ Hoshi Calendar</span><span>Organize cada etapa do seu tempo.</span></footer>
</main><script src="js/app.js"></script></body></html>
