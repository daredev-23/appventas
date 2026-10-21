<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/cruddistrito.php';

function responder(int $estado, bool $ok, string $mensaje): void
{
  http_response_code($estado);
  echo json_encode(['ok' => $ok, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  responder(405, false, 'Método no permitido.');
}

$codigo = trim($_POST['codigo'] ?? '');
if ($codigo === '') {
  responder(400, false, 'Falta el código del distrito.');
}

try {
  if (CrudDistrito::obtener($codigo) === null) {
    responder(404, false, 'El distrito no existe.');
  }

  $n = CrudDistrito::contarClientes($codigo);
  if ($n > 0) {
    responder(409, false, "No se puede eliminar: tiene $n cliente(s) asociado(s).");
  }

  CrudDistrito::eliminar($codigo);
  responder(200, true, 'Distrito eliminado.');
} catch (PDOException $e) {
  responder(500, false, 'No se pudo eliminar el distrito.');
}
