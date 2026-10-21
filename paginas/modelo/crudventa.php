<?php
require_once __DIR__ . '/conexion.php';

// Error "de negocio": algo que el usuario puede corregir (sin stock, cliente inexistente...)
class VentaException extends RuntimeException {}

class CrudVenta
{
  // Lista de ventas, con búsqueda opcional por cliente, fecha (2026-10-17) o número
  public static function listar(string $buscar = ''): array
  {
    $sql = "SELECT v.id_venta, v.fecha_venta,
                   CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS cliente,
                   v.total
            FROM venta v
            INNER JOIN cliente c ON c.codigo_cliente = v.codigo_cliente";
    $params = [];

    if ($buscar !== '') {
      $sql .= " WHERE CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno) LIKE :q1
                   OR DATE_FORMAT(v.fecha_venta, '%Y-%m-%d') LIKE :q2
                   OR CAST(v.id_venta AS CHAR) = :q3";
      $like = '%' . addcslashes($buscar, '%_\\') . '%';
      $params = [':q1' => $like, ':q2' => $like, ':q3' => $buscar];
    }

    $sql .= " ORDER BY v.fecha_venta DESC, v.id_venta DESC";
    $stmt = Conexion::conectar()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  // Detalle de una venta (lo que muestra el botón "Ver")
  public static function detalle(int $id): ?array
  {
    $bd = Conexion::conectar();

    $stmt = $bd->prepare(
      "SELECT v.id_venta, v.fecha_venta, v.total,
              CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS cliente,
              u.nombre AS usuario
       FROM venta v
       INNER JOIN cliente c ON c.codigo_cliente = v.codigo_cliente
       INNER JOIN usuario u ON u.id_usuario = v.id_usuario
       WHERE v.id_venta = :id"
    );
    $stmt->execute([':id' => $id]);
    $venta = $stmt->fetch();
    if (!$venta) {
      return null;
    }

    $stmt = $bd->prepare(
      "SELECT d.codigo_producto, p.producto, d.cantidad, d.precio_unitario, d.subtotal
       FROM detalle_venta d
       INNER JOIN producto p ON p.codigo_producto = d.codigo_producto
       WHERE d.id_venta = :id
       ORDER BY p.producto"
    );
    $stmt->execute([':id' => $id]);

    return ['venta' => $venta, 'lineas' => $stmt->fetchAll()];
  }

  // Listas para el formulario de nueva venta (solo lectura)
  public static function clientesParaLista(): array
  {
    $sql = "SELECT codigo_cliente,
                   CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre
            FROM cliente
            ORDER BY apellido_paterno, apellido_materno, nombre";
    return Conexion::conectar()->query($sql)->fetchAll();
  }

  public static function productosParaLista(): array
  {
    $sql = "SELECT codigo_producto, producto, precio, stock_disponible
            FROM producto
            ORDER BY producto";
    return Conexion::conectar()->query($sql)->fetchAll();
  }

  // Registra una venta completa. $lineas = [['codigo' => 'P0001', 'cantidad' => 2], ...]
  // Devuelve el número de la venta creada.
  public static function crear(string $codigoCliente, int $idUsuario, array $lineas): int
  {
    // Si el mismo producto viene dos veces, se suman las cantidades
    $acum = [];
    foreach ($lineas as $l) {
      $acum[$l['codigo']] = ($acum[$l['codigo']] ?? 0) + $l['cantidad'];
    }
    if (count($acum) === 0) {
      throw new VentaException('La venta debe tener al menos un producto.');
    }

    // Se usa UNA sola conexión: la transacción vive en ella
    $bd = Conexion::conectar();

    $stmt = $bd->prepare("SELECT 1 FROM cliente WHERE codigo_cliente = :c");
    $stmt->execute([':c' => $codigoCliente]);
    if (!$stmt->fetchColumn()) {
      throw new VentaException('El cliente no existe.');
    }

    $bd->beginTransaction();
    try {
      $bd->prepare("INSERT INTO venta (codigo_cliente, id_usuario) VALUES (:cli, :usu)")
         ->execute([':cli' => $codigoCliente, ':usu' => $idUsuario]);
      $idVenta = (int) $bd->lastInsertId();

      // El precio sale de la tabla producto, no de lo que mande la página
      $linea = $bd->prepare(
        "INSERT INTO detalle_venta (id_venta, codigo_producto, cantidad, precio_unitario)
         SELECT :id, codigo_producto, :cant, precio
         FROM producto WHERE codigo_producto = :prod"
      );
      // Descuenta el stock solo si alcanza
      $stock = $bd->prepare(
        "UPDATE producto SET stock_disponible = stock_disponible - :cant1
         WHERE codigo_producto = :prod AND stock_disponible >= :cant2"
      );

      foreach ($acum as $codigo => $cantidad) {
        $linea->execute([':id' => $idVenta, ':cant' => $cantidad, ':prod' => $codigo]);
        if ($linea->rowCount() === 0) {
          throw new VentaException("El producto $codigo no existe.");
        }

        $stock->execute([':cant1' => $cantidad, ':cant2' => $cantidad, ':prod' => $codigo]);
        if ($stock->rowCount() === 0) {
          throw new VentaException("No hay stock suficiente del producto $codigo.");
        }
      }

      // Total = suma de los subtotales de las líneas
      $bd->prepare(
        "UPDATE venta
         SET total = (SELECT COALESCE(SUM(subtotal), 0) FROM detalle_venta WHERE id_venta = :id1)
         WHERE id_venta = :id2"
      )->execute([':id1' => $idVenta, ':id2' => $idVenta]);

      $bd->commit();
      return $idVenta;
    } catch (Throwable $e) {
      $bd->rollBack();
      throw $e;
    }
  }

  // Elimina una venta y devuelve el stock de sus líneas
  public static function eliminar(int $id): bool
  {
    $bd = Conexion::conectar();
    $bd->beginTransaction();
    try {
      $bd->prepare(
        "UPDATE producto p
         INNER JOIN detalle_venta d ON d.codigo_producto = p.codigo_producto
         SET p.stock_disponible = p.stock_disponible + d.cantidad
         WHERE d.id_venta = :id"
      )->execute([':id' => $id]);

      $stmt = $bd->prepare("DELETE FROM venta WHERE id_venta = :id");
      $stmt->execute([':id' => $id]);

      $bd->commit();
      return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
      $bd->rollBack();
      throw $e;
    }
  }
}
