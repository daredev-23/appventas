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

$modo   = $_POST['modo'] ?? '';
$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');

if (!in_array($modo, ['crear', 'editar'], true)) {
  responder(400, false, 'Modo no válido.');
}
if (!preg_match('/^\d{2}$/', $codigo)) {
  responder(400, false, 'El código debe tener exactamente 2 dígitos.');
}
if ($nombre === '' || mb_strlen($nombre) > 40) {
  responder(400, false, 'El nombre es obligatorio y admite hasta 40 caracteres.');
}

try {
  $existe = CrudDepartamento::obtener($codigo) !== null;

  if ($modo === 'crear') {
    if ($existe) {
      responder(409, false, 'Ya existe un departamento con ese código.');
    }
    CrudDepartamento::insertar($codigo, $nombre);
    responder(201, true, 'Departamento creado.');
  }

  if (!$existe) {
    responder(404, false, 'El departamento no existe.');
  }
  CrudDepartamento::actualizar($codigo, $nombre);
  responder(200, true, 'Departamento actualizado.');
} catch (PDOException $e) {
  responder(500, false, 'No se pudo guardar el departamento.');
}
