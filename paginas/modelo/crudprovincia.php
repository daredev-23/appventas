<?php
require_once __DIR__ . '/conexion.php';

class CrudProvincia
{
  // Todas las provincias, con el nombre de su departamento
  public static function listar(): array
  {
    $sql = "SELECT p.codigo_provincia, p.provincia,
                   p.codigo_departamento, d.departamento
            FROM provincia p
            INNER JOIN departamento d
                    ON d.codigo_departamento = p.codigo_departamento
            ORDER BY p.codigo_provincia";
    return Conexion::conectar()->query($sql)->fetchAll();
  }

  // Una sola provincia (para rellenar el formulario al editar)
  public static function obtener(string $codigo): ?array
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT codigo_provincia, provincia, codigo_departamento
       FROM provincia WHERE codigo_provincia = :codigo"
    );
    $stmt->execute([':codigo' => $codigo]);
    return $stmt->fetch() ?: null;
  }

  public static function insertar(string $codigo, string $nombre, string $codigoDep): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO provincia (codigo_provincia, provincia, codigo_departamento)
       VALUES (:codigo, :nombre, :dep)"
    );
    return $stmt->execute([
      ':codigo' => $codigo,
      ':nombre' => $nombre,
      ':dep'    => $codigoDep
    ]);
  }

  // Solo se cambia el nombre: el departamento va dentro del código
  public static function actualizar(string $codigo, string $nombre): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE provincia SET provincia = :nombre
       WHERE codigo_provincia = :codigo"
    );
    return $stmt->execute([':codigo' => $codigo, ':nombre' => $nombre]);
  }

  // Cuántos distritos dependen de esta provincia
  public static function contarDistritos(string $codigo): int
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT COUNT(*) FROM distrito WHERE codigo_provincia = :codigo"
    );
    $stmt->execute([':codigo' => $codigo]);
    return (int) $stmt->fetchColumn();
  }

  public static function eliminar(string $codigo): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "DELETE FROM provincia WHERE codigo_provincia = :codigo"
    );
    return $stmt->execute([':codigo' => $codigo]);
  }

  // Provincias de un solo departamento (para las listas encadenadas)
  public static function listarPorDepartamento(string $dep): array
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT codigo_provincia, provincia, codigo_departamento
       FROM provincia
       WHERE codigo_departamento = :dep
       ORDER BY codigo_provincia"
    );
    $stmt->execute([':dep' => $dep]);
    return $stmt->fetchAll();
  }
}
