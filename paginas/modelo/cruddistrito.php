<?php
require_once __DIR__ . '/conexion.php';

class CrudDistrito
{
  // Todos los distritos, con su provincia y su departamento
  public static function listar(): array
  {
    $sql = "SELECT d.codigo_distrito, d.distrito,
                   d.codigo_provincia, p.provincia,
                   p.codigo_departamento, dep.departamento
            FROM distrito d
            INNER JOIN provincia p
                    ON p.codigo_provincia = d.codigo_provincia
            INNER JOIN departamento dep
                    ON dep.codigo_departamento = p.codigo_departamento
            ORDER BY d.codigo_distrito";
    return Conexion::conectar()->query($sql)->fetchAll();
  }

  // Un solo distrito (para rellenar el formulario al editar)
  public static function obtener(string $codigo): ?array
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT codigo_distrito, distrito, codigo_provincia
       FROM distrito WHERE codigo_distrito = :codigo"
    );
    $stmt->execute([':codigo' => $codigo]);
    return $stmt->fetch() ?: null;
  }

  public static function insertar(string $codigo, string $nombre, string $codigoProv): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO distrito (codigo_distrito, distrito, codigo_provincia)
       VALUES (:codigo, :nombre, :prov)"
    );
    return $stmt->execute([
      ':codigo' => $codigo,
      ':nombre' => $nombre,
      ':prov'   => $codigoProv
    ]);
  }

  // Solo se cambia el nombre: la provincia va dentro del código
  public static function actualizar(string $codigo, string $nombre): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE distrito SET distrito = :nombre
       WHERE codigo_distrito = :codigo"
    );
    return $stmt->execute([':codigo' => $codigo, ':nombre' => $nombre]);
  }

  // Cuántos clientes viven en este distrito
  public static function contarClientes(string $codigo): int
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT COUNT(*) FROM cliente WHERE codigo_distrito = :codigo"
    );
    $stmt->execute([':codigo' => $codigo]);
    return (int) $stmt->fetchColumn();
  }

  public static function eliminar(string $codigo): bool
  {
    $stmt = Conexion::conectar()->prepare(
      "DELETE FROM distrito WHERE codigo_distrito = :codigo"
    );
    return $stmt->execute([':codigo' => $codigo]);
  }
}
