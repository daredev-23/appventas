<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/crudprovincia.php';

$dep = trim($_GET['departamento'] ?? '');

if (!preg_match('/^\d{2}$/', $dep)) {
  http_response_code(400);
  echo json_encode(
    ['ok' => false, 'mensaje' => 'Departamento no válido.'],
    JSON_UNESCAPED_UNICODE
  );
  exit;
}

try {
  echo json_encode(
    ['ok' => true, 'datos' => CrudProvincia::listarPorDepartamento($dep)],
    JSON_UNESCAPED_UNICODE
  );
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(
    ['ok' => false, 'mensaje' => 'No se pudo leer la lista de provincias.'],
    JSON_UNESCAPED_UNICODE
  );
}
