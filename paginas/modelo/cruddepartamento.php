<?php
require_once __DIR__ . '/conexion.php';

class CrudDepartamento
{
  // Todos los departamentos, ordenados por código
  public static function listar(): array
  {
    $sql = "SELECT codigo_departamento, departamento
            FROM departamento
            ORDER BY codigo_departamento";
    return Conexion::conectar()->query($sql)->fetchAll();
  }

  // Un solo departamento (para rellenar el formulario al editar)
  public static function obtener(string $codigo): ?array
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT codigo_departamento, departamento
       FROM departamento WHERE codigo_departamento = :codigo"
    );
    $stmt->execute([':codigo' => $codigo]);
    return $stmt->fetch() ?: null;
  }

  public static function insertar(string $codigo, string $nombre): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO departamento (codigo_departamento, departamento)
       VALUES (:codigo, :nombre)"
    );
    return $stmt->execute([':codigo' => $codigo, ':nombre' => $nombre]);
  }

  public static function actualizar(string $codigo, string $nombre): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE departamento SET departamento = :nombre
       WHERE codigo_departamento = :codigo"
    );
    return $stmt->execute([':codigo' => $codigo, ':nombre' => $nombre]);
  }

  // Cuántas provincias dependen de este departamento
  public static function contarProvincias(string $codigo): int
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT COUNT(*) FROM provincia WHERE codigo_departamento = :codigo"
    );
    $stmt->execute([':codigo' => $codigo]);
    return (int) $stmt->fetchColumn();
  }

  public static function eliminar(string $codigo): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "DELETE FROM departamento WHERE codigo_departamento = :codigo"
    );
    return $stmt->execute([':codigo' => $codigo]);
  }
}
