<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/crudventa.php';

$buscar = trim($_GET['buscar'] ?? '');

if (mb_strlen($buscar) > 60) {
  http_response_code(400);
  echo json_encode(
    ['ok' => false, 'mensaje' => 'El texto de búsqueda es demasiado largo.'],
    JSON_UNESCAPED_UNICODE
  );
  exit;
}

try {
  echo json_encode(
    ['ok' => true, 'datos' => CrudVenta::listar($buscar)],
    JSON_UNESCAPED_UNICODE
  );
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(
    ['ok' => false, 'mensaje' => 'No se pudo leer la lista de ventas.'],
    JSON_UNESCAPED_UNICODE
  );
}
