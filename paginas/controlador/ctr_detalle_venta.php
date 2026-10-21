<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/crudventa.php';

function responder(int $estado, array $cuerpo): void
{
  http_response_code($estado);
  echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
  exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
  responder(400, ['ok' => false, 'mensaje' => 'Número de venta no válido.']);
}

try {
  $detalle = CrudVenta::detalle($id);
  if ($detalle === null) {
    responder(404, ['ok' => false, 'mensaje' => 'La venta no existe.']);
  }
  responder(200, ['ok' => true, 'datos' => $detalle]);
} catch (PDOException $e) {
  responder(500, ['ok' => false, 'mensaje' => 'No se pudo leer el detalle de la venta.']);
}
