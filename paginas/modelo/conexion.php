<?php
class Conexion
{
  public static function conectar(): PDO
  {
    $c = require __DIR__ . '/credenciales.php';
    $dsn = "mysql:host={$c['host']};port={$c['puerto']};dbname={$c['base']};charset=utf8mb4";
    $opciones = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      \Pdo\Mysql::ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt'
    ];
    return new PDO($dsn, $c['usuario'], $c['clave'], $opciones);
  }
}
