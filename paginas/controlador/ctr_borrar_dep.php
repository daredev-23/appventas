<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/cruddepartamento.php';

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
  responder(400, false, 'Falta el código del departamento.');
}

try {
  if (CrudDepartamento::obtener($codigo) === null) {
    responder(404, false, 'El departamento no existe.');
  }

  $n = CrudDepartamento::contarProvincias($codigo);
  if ($n > 0) {
    responder(409, false, "No se puede eliminar: tiene $n provincia(s) asociada(s).");
  }

  CrudDepartamento::eliminar($codigo);
  responder(200, true, 'Departamento eliminado.');
} catch (PDOException $e) {
  responder(500, false, 'No se pudo eliminar el departamento.');
}
