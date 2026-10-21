<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/crudprovincia.php';
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

$modo   = $_POST['modo'] ?? '';
$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$dep    = trim($_POST['departamento'] ?? '');

if (!in_array($modo, ['crear', 'editar'], true)) {
  responder(400, false, 'Modo no válido.');
}
if (!preg_match('/^\d{4}$/', $codigo)) {
  responder(400, false, 'El código debe tener exactamente 4 dígitos.');
}
if ($nombre === '' || mb_strlen($nombre) > 40) {
  responder(400, false, 'El nombre es obligatorio y admite hasta 40 caracteres.');
}

try {
  $existe = CrudProvincia::obtener($codigo) !== null;

  if ($modo === 'crear') {
    if (!preg_match('/^\d{2}$/', $dep)) {
      responder(400, false, 'Elige un departamento.');
    }
    if (CrudDepartamento::obtener($dep) === null) {
      responder(400, false, 'El departamento elegido no existe.');
    }
    if (!str_starts_with($codigo, $dep)) {
      responder(400, false, "El código debe empezar con $dep, el código del departamento.");
    }
    if ($existe) {
      responder(409, false, 'Ya existe una provincia con ese código.');
    }
    CrudProvincia::insertar($codigo, $nombre, $dep);
    responder(201, true, 'Provincia creada.');
  }

  if (!$existe) {
    responder(404, false, 'La provincia no existe.');
  }
  CrudProvincia::actualizar($codigo, $nombre);
  responder(200, true, 'Provincia actualizada.');
} catch (PDOException $e) {
  responder(500, false, 'No se pudo guardar la provincia.');
}
