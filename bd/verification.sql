-- 1. COMPROBACIONES: cada una debe dar 0 problemas
SELECT 'provincia sin departamento' AS comprobacion, COUNT(*) AS problemas
  FROM provincia p LEFT JOIN departamento d ON d.codigo_departamento = p.codigo_departamento
  WHERE d.codigo_departamento IS NULL
UNION ALL
SELECT 'distrito sin provincia', COUNT(*)
  FROM distrito x LEFT JOIN provincia p ON p.codigo_provincia = x.codigo_provincia
  WHERE p.codigo_provincia IS NULL
UNION ALL
SELECT 'producto sin marca', COUNT(*)
  FROM producto p LEFT JOIN marca m ON m.codigo_marca = p.codigo_marca
  WHERE m.codigo_marca IS NULL
UNION ALL
SELECT 'producto sin categoria', COUNT(*)
  FROM producto p LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
  WHERE c.codigo_categoria IS NULL
UNION ALL
SELECT 'cliente sin distrito', COUNT(*)
  FROM cliente c LEFT JOIN distrito d ON d.codigo_distrito = c.codigo_distrito
  WHERE d.codigo_distrito IS NULL
UNION ALL
SELECT 'venta sin cliente', COUNT(*)
  FROM venta v LEFT JOIN cliente c ON c.codigo_cliente = v.codigo_cliente
  WHERE c.codigo_cliente IS NULL
UNION ALL
SELECT 'venta sin usuario', COUNT(*)
  FROM venta v LEFT JOIN usuario u ON u.id_usuario = v.id_usuario
  WHERE u.id_usuario IS NULL
UNION ALL
SELECT 'detalle sin venta', COUNT(*)
  FROM detalle_venta d LEFT JOIN venta v ON v.id_venta = d.id_venta
  WHERE v.id_venta IS NULL
UNION ALL
SELECT 'detalle sin producto', COUNT(*)
  FROM detalle_venta d LEFT JOIN producto p ON p.codigo_producto = d.codigo_producto
  WHERE p.codigo_producto IS NULL
UNION ALL
SELECT 'venta sin detalle', COUNT(*)
  FROM venta v LEFT JOIN detalle_venta d ON d.id_venta = v.id_venta
  WHERE d.id_venta IS NULL
UNION ALL
SELECT 'total de venta distinto a la suma de su detalle', COUNT(*)
  FROM venta v
  WHERE v.total <> (SELECT COALESCE(SUM(d.subtotal), 0) FROM detalle_venta d WHERE d.id_venta = v.id_venta)
UNION ALL
SELECT 'precio del detalle distinto al precio del producto', COUNT(*)
  FROM detalle_venta d JOIN producto p ON p.codigo_producto = d.codigo_producto
  WHERE d.precio_unitario <> p.precio
UNION ALL
SELECT 'codigo de provincia que no empieza con su departamento', COUNT(*)
  FROM provincia WHERE LEFT(codigo_provincia, 2) <> codigo_departamento
UNION ALL
SELECT 'codigo de distrito que no empieza con su provincia', COUNT(*)
  FROM distrito WHERE LEFT(codigo_distrito, 4) <> codigo_provincia
UNION ALL
SELECT 'producto con stock negativo, costo <= 0 o ganancia fuera de 0 a 1', COUNT(*)
  FROM producto WHERE stock_disponible < 0 OR costo <= 0 OR ganancia < 0 OR ganancia >= 1
UNION ALL
SELECT 'detalle con cantidad <= 0', COUNT(*)
  FROM detalle_venta WHERE cantidad <= 0
UNION ALL
SELECT 'venta con fecha futura', COUNT(*)
  FROM venta WHERE fecha_venta > NOW()
UNION ALL
SELECT 'usuario con clave que no es un hash', COUNT(*)
  FROM usuario WHERE clave NOT LIKE '$2y$%';

-- 2. CONTEO DE FILAS: debe coincidir con la columna esperado
SELECT 'departamento' AS tabla, COUNT(*) AS filas, 8 AS esperado FROM departamento
UNION ALL SELECT 'provincia', COUNT(*), 10 FROM provincia
UNION ALL SELECT 'distrito', COUNT(*), 21 FROM distrito
UNION ALL SELECT 'marca', COUNT(*), 13 FROM marca
UNION ALL SELECT 'categoria', COUNT(*), 8 FROM categoria
UNION ALL SELECT 'producto', COUNT(*), 30 FROM producto
UNION ALL SELECT 'cliente', COUNT(*), 20 FROM cliente
UNION ALL SELECT 'usuario', COUNT(*), 5 FROM usuario
UNION ALL SELECT 'venta', COUNT(*), 14 FROM venta
UNION ALL SELECT 'detalle_venta', COUNT(*), 28 FROM detalle_venta;

-- 3. RELACIONES ENTRE TABLAS: debe dar 9
SELECT COUNT(*) AS relaciones
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = 'FOREIGN KEY';
