<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../modelo/crudventa.php';

try {
  echo json_encode(
    [
      'ok'        => true,
      'clientes'  => CrudVenta::clientesParaLista(),
      'productos' => CrudVenta::productosParaLista()
    ],
    JSON_UNESCAPED_UNICODE
  );
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(
    ['ok' => false, 'mensaje' => 'No se pudieron leer los clientes y productos.'],
    JSON_UNESCAPED_UNICODE
  );
}
