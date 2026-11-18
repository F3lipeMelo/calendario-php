<?php
require __DIR__ . '/conexao.php';
require __DIR__ . '/lib/calendar.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método inválido.'); }
verify_csrf();
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('Marcação inválida.'); }
$stmt = $conexao->prepare('UPDATE compromissos SET concluido = NOT concluido WHERE id = :id');
$stmt->execute([':id' => $id]);
header('Location: ' . item_url($_POST));
exit;
