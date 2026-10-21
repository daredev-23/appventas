<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/crudventa.php';

function responder(int $estado, bool $ok, string $mensaje): void
{
  http_response_code($estado);
  echo json_encode(['ok' => $ok, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  responder(405, false, 'Método no permitido.');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
  responder(400, false, 'Número de venta no válido.');
}

try {
  if (!CrudVenta::eliminar($id)) {
    responder(404, false, 'La venta no existe.');
  }
  responder(200, true, 'Venta eliminada y stock devuelto.');
} catch (PDOException $e) {
  responder(500, false, 'No se pudo eliminar la venta.');
}
