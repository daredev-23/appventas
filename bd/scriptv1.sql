-- Base de datos: appventas_ds500
-- ATENCIÓN: si la base ya existe, se BORRA completa y se crea de nuevo.

DROP DATABASE IF EXISTS appventas_ds500;

CREATE DATABASE appventas_ds500
   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE appventas_ds500;

-- Ubigeo (departamento > provincia > distrito)
CREATE TABLE departamento (
  codigo_departamento CHAR(2)     NOT NULL,
  departamento        VARCHAR(40) NOT NULL,
  PRIMARY KEY (codigo_departamento)
) ENGINE=InnoDB;

CREATE TABLE provincia (
  codigo_provincia    CHAR(4)     NOT NULL,
  provincia           VARCHAR(40) NOT NULL,
  codigo_departamento CHAR(2)     NOT NULL,
  PRIMARY KEY (codigo_provincia),
  CONSTRAINT fk_prov_dep FOREIGN KEY (codigo_departamento)
    REFERENCES departamento (codigo_departamento)
) ENGINE=InnoDB;

CREATE TABLE distrito (
  codigo_distrito  CHAR(6)     NOT NULL,
  distrito         VARCHAR(40) NOT NULL,
  codigo_provincia CHAR(4)     NOT NULL,
  PRIMARY KEY (codigo_distrito),
  CONSTRAINT fk_dist_prov FOREIGN KEY (codigo_provincia)
    REFERENCES provincia (codigo_provincia)
) ENGINE=InnoDB;

-- Catálogos
CREATE TABLE marca (
  codigo_marca CHAR(5)     NOT NULL,
  marca        VARCHAR(40) NOT NULL,
  PRIMARY KEY (codigo_marca)
) ENGINE=InnoDB;

CREATE TABLE categoria (
  codigo_categoria CHAR(5)     NOT NULL,
  categoria        VARCHAR(40) NOT NULL,
  PRIMARY KEY (codigo_categoria)
) ENGINE=InnoDB;

-- Producto
-- "ganancia" se guarda como fracción (0.1625 = 16.25 %).
-- "precio" se calcula solo: costo + ganancia.
CREATE TABLE producto (
  codigo_producto  CHAR(5)       NOT NULL,
  producto         VARCHAR(40)   NOT NULL,
  stock_disponible INT           NOT NULL DEFAULT 0,
  costo            DECIMAL(8,2)  NOT NULL,
  ganancia         DECIMAL(5,4)  NOT NULL,
  precio           DECIMAL(10,2) GENERATED ALWAYS AS (ROUND(costo * (1 + ganancia), 2)) STORED,
  codigo_marca     CHAR(5)       NOT NULL,
  codigo_categoria CHAR(5)       NOT NULL,
  PRIMARY KEY (codigo_producto),
  CONSTRAINT fk_prod_marca FOREIGN KEY (codigo_marca)
    REFERENCES marca (codigo_marca),
  CONSTRAINT fk_prod_cat FOREIGN KEY (codigo_categoria)
    REFERENCES categoria (codigo_categoria)
) ENGINE=InnoDB;

-- Cliente
CREATE TABLE cliente (
  codigo_cliente   CHAR(5)     NOT NULL,
  nombre           VARCHAR(40) NOT NULL,
  apellido_paterno VARCHAR(30) NOT NULL,
  apellido_materno VARCHAR(30) NOT NULL,
  fecha_nacimiento DATE        NOT NULL,
  direccion        VARCHAR(60) NOT NULL,
  correo           VARCHAR(60) NULL,
  codigo_distrito  CHAR(6)     NOT NULL,
  PRIMARY KEY (codigo_cliente),
  CONSTRAINT fk_cli_dist FOREIGN KEY (codigo_distrito)
    REFERENCES distrito (codigo_distrito)
) ENGINE=InnoDB;

-- Usuario (inicio de sesión). La clave se guarda con password_hash() de PHP.
CREATE TABLE usuario (
  id_usuario     INT          NOT NULL AUTO_INCREMENT,
  nombre         VARCHAR(60)  NOT NULL,
  correo         VARCHAR(60)  NOT NULL UNIQUE,
  clave          VARCHAR(255) NOT NULL,
  rol            ENUM('admin','vendedor') NOT NULL DEFAULT 'vendedor',
  estado         TINYINT(1)   NOT NULL DEFAULT 1,
  fecha_registro TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_usuario)
) ENGINE=InnoDB;

-- Ventas (cabecera) y su detalle (una fila por producto vendido)
CREATE TABLE venta (
  id_venta       INT           NOT NULL AUTO_INCREMENT,
  fecha_venta    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  codigo_cliente CHAR(5)       NOT NULL,
  id_usuario     INT           NOT NULL,
  total          DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (id_venta),
  CONSTRAINT fk_venta_cli FOREIGN KEY (codigo_cliente)
    REFERENCES cliente (codigo_cliente),
  CONSTRAINT fk_venta_usu FOREIGN KEY (id_usuario)
    REFERENCES usuario (id_usuario)
) ENGINE=InnoDB;

CREATE TABLE detalle_venta (
  id_venta        INT           NOT NULL,
  codigo_producto CHAR(5)       NOT NULL,
  cantidad        INT           NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal        DECIMAL(10,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
  PRIMARY KEY (id_venta, codigo_producto),
  CONSTRAINT fk_det_venta FOREIGN KEY (id_venta)
    REFERENCES venta (id_venta) ON DELETE CASCADE,
  CONSTRAINT fk_det_prod FOREIGN KEY (codigo_producto)
    REFERENCES producto (codigo_producto)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- DATOS DE PRUEBA
-- ---------------------------------------------------------------------

INSERT INTO departamento VALUES
('04','Arequipa'),
('07','Callao'),
('08','Cusco'),
('11','Ica'),
('13','La Libertad'),
('14','Lambayeque'),
('15','Lima'),
('20','Piura');

INSERT INTO provincia VALUES
('0401','Arequipa','04'),
('0701','Callao','07'),
('0801','Cusco','08'),
('1101','Ica','11'),
('1301','Trujillo','13'),
('1401','Chiclayo','14'),
('1501','Lima','15'),
('1502','Cañete','15'),
('1508','Huaral','15'),
('2001','Piura','20');

INSERT INTO distrito VALUES
('040129','Yanahuara','0401'),
('070101','Callao','0701'),
('070102','Bellavista','0701'),
('070104','La Perla','0701'),
('070106','Ventanilla','0701'),
('080101','Cusco','0801'),
('110101','Ica','1101'),
('130101','Trujillo','1301'),
('140101','Chiclayo','1401'),
('150101','Lima','1501'),
('150103','Ate','1501'),
('150110','Comas','1501'),
('150117','Los Olivos','1501'),
('150122','Miraflores','1501'),
('150131','San Isidro','1501'),
('150132','San Juan de Lurigancho','1501'),
('150135','San Martín de Porres','1501'),
('150140','Santiago de Surco','1501'),
('150201','San Vicente de Cañete','1502'),
('150801','Huaral','1508'),
('200101','Piura','2001');

INSERT INTO marca VALUES
('M0001','LG'),
('M0002','Acer'),
('M0003','Sony'),
('M0004','Samsung'),
('M0005','Lenovo'),
('M0006','HP'),
('M0007','Xiaomi'),
('M0008','Philips'),
('M0009','Oster'),
('M0010','Panasonic'),
('M0011','Dell'),
('M0012','Asus'),
('M0013','JBL');

INSERT INTO categoria VALUES
('C0001','Electrodomésticos'),
('C0002','Equipo de cómputo'),
('C0003','Telefonía'),
('C0004','Audio y video'),
('C0005','Cocina'),
('C0006','Accesorios de cómputo'),
('C0007','Gaming'),
('C0008','Hogar inteligente');

INSERT INTO producto
  (codigo_producto, producto, stock_disponible, costo, ganancia, codigo_marca, codigo_categoria) VALUES
('P0001','Lavadora 13Kg',                  100, 1420.00, 0.2015, 'M0001','C0001'),
('P0002','Laptop Core i5 8GB SSD 512 GB',   54, 1762.45, 0.1635, 'M0002','C0002'),
('P0003','Televisor 65'' QLED',             73, 2669.17, 0.1625, 'M0003','C0004'),
('P0004','Equipo de sonido 250W',           15,   43.85, 0.1289, 'M0001','C0004'),
('P0005','Galaxy A45',                     105,  980.00, 0.1800, 'M0004','C0003'),
('P0006','Laptop Core i3 16GB 1TB SSD',     24, 1258.37, 0.1524, 'M0002','C0002'),
('P0007','Tablet Lenovo Tab M10',           40,  520.00, 0.2000, 'M0005','C0002'),
('P0008','Impresora HP Smart Tank',         18,  690.50, 0.1700, 'M0006','C0002'),
('P0009','Refrigeradora No Frost 300L',     22, 1650.00, 0.1800, 'M0001','C0001'),
('P0010','Licuadora 600W',                  80,  185.00, 0.2200, 'M0009','C0005'),
('P0011','Microondas 25L',                  35,  320.00, 0.2000, 'M0004','C0005'),
('P0012','Laptop Dell Inspiron 15 Ryzen 7', 18, 2100.00, 0.1500, 'M0011','C0002'),
('P0013','Laptop Asus Vivobook 14',         27, 1890.00, 0.1600, 'M0012','C0002'),
('P0014','Monitor 24 pulgadas Full HD',     44,  520.00, 0.1900, 'M0004','C0002'),
('P0015','Mouse inalámbrico',              150,   28.00, 0.3500, 'M0005','C0006'),
('P0016','Teclado mecánico',                60,   95.00, 0.3000, 'M0006','C0006'),
('P0017','Auriculares Bluetooth',           75,  210.00, 0.2500, 'M0013','C0004'),
('P0018','Parlante portátil Charge',        40,  380.00, 0.2200, 'M0013','C0004'),
('P0019','Televisor 50 pulgadas 4K',        30, 1450.00, 0.1700, 'M0001','C0004'),
('P0020','Smartphone Redmi Note 13',        90,  690.00, 0.2000, 'M0007','C0003'),
('P0021','Galaxy S24',                      25, 2850.00, 0.1400, 'M0004','C0003'),
('P0022','Smartwatch Redmi Watch',          65,  240.00, 0.2800, 'M0007','C0003'),
('P0023','Aspiradora robot',                14,  780.00, 0.2100, 'M0007','C0008'),
('P0024','Foco inteligente WiFi',          200,   35.00, 0.4000, 'M0008','C0008'),
('P0025','Plancha a vapor',                 55,  110.00, 0.2600, 'M0008','C0005'),
('P0026','Cafetera automática',             33,  245.00, 0.2400, 'M0009','C0005'),
('P0027','Laptop gamer HP Victus',          12, 3400.00, 0.1300, 'M0006','C0007'),
('P0028','Silla gamer ergonómica',          20,  650.00, 0.2200, 'M0012','C0007'),
('P0029','Impresora multifuncional',        28,  430.00, 0.1800, 'M0006','C0002'),
('P0030','Barra de sonido 2.1',             17,  720.00, 0.2000, 'M0003','C0004');

INSERT INTO cliente VALUES
('C0001','Carlos','Ramos','Veras','1995-03-14','Jr. Bolognesi 120','carlos.ramos@mail.com','110101'),
('C0002','Rosa','Collantes','Flores','2000-05-30','Jr. Álamos 354',NULL,'150135'),
('C0003','Felipe','Montes','Salas','1998-08-21','Av. Perú 875','felipe.montes@mail.com','150135'),
('C0004','Viviana','Gonzáles','Roca','1999-01-09','Calle Los Pinos 45',NULL,'150201'),
('C0005','Erika','Flores','Casas','2001-11-20','Av. Benavides 2354',NULL,'070101'),
('C0006','Lucía','Torres','Mendoza','1997-02-11','Av. Arequipa 1520','lucia.torres@mail.com','150122'),
('C0007','Jorge','Paredes','Quispe','1990-07-23','Jr. Cusco 410','jorge.paredes@mail.com','150110'),
('C0008','María','Huamán','Ccori','1985-12-05','Calle Los Cedros 88',NULL,'080101'),
('C0009','Diego','Salazar','Rojas','2002-04-17','Av. La Marina 2300','diego.salazar@mail.com','070104'),
('C0010','Andrea','Vargas','Luna','1999-09-30','Jr. Huallaga 215','andrea.vargas@mail.com','150101'),
('C0011','Luis','Chávez','Ortiz','1993-06-14','Av. Universitaria 1801',NULL,'150117'),
('C0012','Patricia','Rivera','Díaz','1988-03-08','Calle Las Flores 67','patricia.rivera@mail.com','150131'),
('C0013','Miguel','Soto','Aguilar','1996-11-26','Av. Pardo 540','miguel.soto@mail.com','150140'),
('C0014','Carmen','Delgado','Prado','1980-01-19','Mz. B Lt. 12 Urb. Santa Rosa',NULL,'070106'),
('C0015','Renzo','Castañeda','Vega','2000-08-02','Av. España 920','renzo.castaneda@mail.com','130101'),
('C0016','Sofía','Mendoza','Ríos','2003-05-21','Av. Grau 1105','sofia.mendoza@mail.com','200101'),
('C0017','Kevin','Palomino','Lazo','1994-10-10','Jr. Lima 330',NULL,'150103'),
('C0018','Daniela','Cruz','Barrios','1998-12-28','Av. Ejército 745','daniela.cruz@mail.com','040129'),
('C0019','Héctor','Valdivia','Neyra','1975-04-04','Calle San Martín 190',NULL,'140101'),
('C0020','Gabriela','Espinoza','León','2001-07-15','Av. Perú 2210','gabriela.espinoza@mail.com','150132');

-- Todos con la clave "password" (guardada como hash)
INSERT INTO usuario (nombre, correo, clave, rol) VALUES
('Administrador',      'admin@appventas.pe',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Vendedor Demo',      'vendedor@appventas.pe',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor'),
('Ana Ruiz',           'ana.ruiz@appventas.pe',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor'),
('Carlos Medina',      'carlos.medina@appventas.pe',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor'),
('Supervisor General', 'supervisor@appventas.pe',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Ventas en orden de fecha. El total se calcula más abajo, con el detalle.
INSERT INTO venta (id_venta, fecha_venta, codigo_cliente, id_usuario) VALUES
( 1, '2026-08-12 11:20:00', 'C0006', 2),
( 2, '2026-08-19 15:05:00', 'C0007', 3),
( 3, '2026-08-27 09:45:00', 'C0008', 3),
( 4, '2026-09-02 17:30:00', 'C0009', 4),
( 5, '2026-09-09 12:10:00', 'C0010', 2),
( 6, '2026-09-15 10:00:00', 'C0011', 5),
( 7, '2026-09-21 18:25:00', 'C0012', 4),
( 8, '2026-09-28 13:40:00', 'C0013', 3),
( 9, '2026-10-01 10:15:00', 'C0001', 1),
(10, '2026-10-03 16:15:00', 'C0014', 2),
(11, '2026-10-05 16:40:00', 'C0003', 2),
(12, '2026-10-08 11:50:00', 'C0015', 5),
(13, '2026-10-13 14:30:00', 'C0016', 4),
(14, '2026-10-17 10:20:00', 'C0017', 3);

-- El precio de cada línea se toma del precio actual del producto.
INSERT INTO detalle_venta (id_venta, codigo_producto, cantidad, precio_unitario)
SELECT d.id_venta, d.codigo_producto, d.cantidad, p.precio
FROM (
  SELECT  1 AS id_venta, 'P0009' AS codigo_producto, 1 AS cantidad UNION ALL
  SELECT  1, 'P0010', 2 UNION ALL
  SELECT  2, 'P0012', 1 UNION ALL
  SELECT  2, 'P0015', 2 UNION ALL
  SELECT  2, 'P0016', 1 UNION ALL
  SELECT  3, 'P0020', 1 UNION ALL
  SELECT  3, 'P0022', 1 UNION ALL
  SELECT  4, 'P0019', 1 UNION ALL
  SELECT  4, 'P0030', 1 UNION ALL
  SELECT  5, 'P0026', 1 UNION ALL
  SELECT  5, 'P0025', 2 UNION ALL
  SELECT  5, 'P0011', 1 UNION ALL
  SELECT  6, 'P0027', 1 UNION ALL
  SELECT  6, 'P0028', 1 UNION ALL
  SELECT  7, 'P0021', 1 UNION ALL
  SELECT  8, 'P0014', 2 UNION ALL
  SELECT  8, 'P0015', 2 UNION ALL
  SELECT  9, 'P0003', 1 UNION ALL
  SELECT 10, 'P0017', 2 UNION ALL
  SELECT 10, 'P0018', 1 UNION ALL
  SELECT 11, 'P0002', 1 UNION ALL
  SELECT 12, 'P0013', 1 UNION ALL
  SELECT 12, 'P0029', 1 UNION ALL
  SELECT 13, 'P0023', 1 UNION ALL
  SELECT 13, 'P0024', 4 UNION ALL
  SELECT 14, 'P0001', 1 UNION ALL
  SELECT 14, 'P0005', 1 UNION ALL
  SELECT 14, 'P0007', 2
) AS d
JOIN producto p ON p.codigo_producto = d.codigo_producto;

-- Total de cada venta = suma de los subtotales de su detalle.
UPDATE venta v
SET v.total = (SELECT COALESCE(SUM(dv.subtotal), 0)
               FROM detalle_venta dv
               WHERE dv.id_venta = v.id_venta)
WHERE v.id_venta > 0;

-- ---------------------------------------------------------------------
-- VERIFICACIÓN (cantidad de filas esperada entre paréntesis)
-- ---------------------------------------------------------------------
SELECT 'departamento' AS tabla, COUNT(*) AS filas FROM departamento UNION ALL  -- 8
SELECT 'provincia',     COUNT(*) FROM provincia     UNION ALL                  -- 10
SELECT 'distrito',      COUNT(*) FROM distrito      UNION ALL                  -- 21
SELECT 'marca',         COUNT(*) FROM marca         UNION ALL                  -- 13
SELECT 'categoria',     COUNT(*) FROM categoria     UNION ALL                  -- 8
SELECT 'producto',      COUNT(*) FROM producto      UNION ALL                  -- 30
SELECT 'cliente',       COUNT(*) FROM cliente       UNION ALL                  -- 20
SELECT 'usuario',       COUNT(*) FROM usuario       UNION ALL                  -- 5
SELECT 'venta',         COUNT(*) FROM venta         UNION ALL                  -- 14
SELECT 'detalle_venta', COUNT(*) FROM detalle_venta;                           -- 28
