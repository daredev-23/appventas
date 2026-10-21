<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/cruddepartamento.php';

try {
  echo json_encode(
    ['ok' => true, 'datos' => CrudDepartamento::listar()],
    JSON_UNESCAPED_UNICODE
  );
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(
    ['ok' => false, 'mensaje' => 'No se pudo leer la lista de departamentos.'],
    JSON_UNESCAPED_UNICODE
  );
}
