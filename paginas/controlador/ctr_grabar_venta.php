<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/crudventa.php';

// TEMPORAL: mientras no exista el login, toda venta se registra con el usuario 1.
// Cuando exista el login, aquí irá el usuario que inició sesión.
const ID_USUARIO_TEMPORAL = 1;

function responder(int $estado, array $cuerpo): void
{
  http_response_code($estado);
  echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  responder(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
}

$cliente = is_string($_POST['cliente'] ?? null) ? trim($_POST['cliente']) : '';
if (!preg_match('/^C\d{4}$/', $cliente)) {
  responder(400, ['ok' => false, 'mensaje' => 'Elige un cliente.']);
}

$entrada = $_POST['lineas'] ?? [];
if (!is_array($entrada) || count($entrada) === 0) {
  responder(400, ['ok' => false, 'mensaje' => 'Agrega al menos un producto.']);
}
if (count($entrada) > 50) {
  responder(400, ['ok' => false, 'mensaje' => 'Una venta admite como máximo 50 productos distintos.']);
}

$lineas = [];
foreach ($entrada as $l) {
  $codigo = (is_array($l) && is_string($l['codigo'] ?? null)) ? trim($l['codigo']) : '';
  $cantidad = is_array($l)
    ? filter_var($l['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9999]])
    : false;

  if (!preg_match('/^P\d{4}$/', $codigo) || $cantidad === false) {
    responder(400, ['ok' => false, 'mensaje' => 'Hay una línea con producto o cantidad no válidos.']);
  }
  $lineas[] = ['codigo' => $codigo, 'cantidad' => $cantidad];
}

try {
  $id = CrudVenta::crear($cliente, ID_USUARIO_TEMPORAL, $lineas);
  responder(201, ['ok' => true, 'id' => $id, 'mensaje' => "Venta $id registrada."]);
} catch (VentaException $e) {
  responder(409, ['ok' => false, 'mensaje' => $e->getMessage()]);
} catch (PDOException $e) {
  responder(500, ['ok' => false, 'mensaje' => 'No se pudo registrar la venta.']);
}
