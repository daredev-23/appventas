<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/cruddistrito.php';
require_once __DIR__ . '/../modelo/crudprovincia.php';

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
$prov   = trim($_POST['provincia'] ?? '');

if (!in_array($modo, ['crear', 'editar'], true)) {
  responder(400, false, 'Modo no válido.');
}
if (!preg_match('/^\d{6}$/', $codigo)) {
  responder(400, false, 'El código debe tener exactamente 6 dígitos.');
}
if ($nombre === '' || mb_strlen($nombre) > 40) {
  responder(400, false, 'El nombre es obligatorio y admite hasta 40 caracteres.');
}

try {
  $existe = CrudDistrito::obtener($codigo) !== null;

  if ($modo === 'crear') {
    if (!preg_match('/^\d{4}$/', $prov)) {
      responder(400, false, 'Elige una provincia.');
    }
    if (CrudProvincia::obtener($prov) === null) {
      responder(400, false, 'La provincia elegida no existe.');
    }
    if (!str_starts_with($codigo, $prov)) {
      responder(400, false, "El código debe empezar con $prov, el código de la provincia.");
    }
    if ($existe) {
      responder(409, false, 'Ya existe un distrito con ese código.');
    }
    CrudDistrito::insertar($codigo, $nombre, $prov);
    responder(201, true, 'Distrito creado.');
  }

  if (!$existe) {
    responder(404, false, 'El distrito no existe.');
  }
  CrudDistrito::actualizar($codigo, $nombre);
  responder(200, true, 'Distrito actualizado.');
} catch (PDOException $e) {
  responder(500, false, 'No se pudo guardar el distrito.');
}
