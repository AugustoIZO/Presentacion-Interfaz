-- Base de datos ALISBOOK_BD
CREATE DATABASE IF NOT EXISTS alisbook;
USE alisbook;

-- ============================================
-- TABLA: CATEGORIAS
-- ============================================
DROP TABLE IF EXISTS `CATEGORIAS`;
CREATE TABLE `CATEGORIAS` (
  `IDCATEGORIA` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIPCION` varchar(100) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  PRIMARY KEY (`IDCATEGORIA`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `CATEGORIAS` VALUES
(1,'Libros','Activo','2025-08-12 08:10:18'),
(2,'Utiles','Activo','2025-08-12 08:10:36');

-- ============================================
-- TABLA: CLIENTES
-- ============================================
DROP TABLE IF EXISTS `CLIENTES`;
CREATE TABLE `CLIENTES` (
  `IDCLIENTE` int(11) NOT NULL AUTO_INCREMENT,
  `DOCUMENTO` varchar(20) DEFAULT NULL,
  `NOMBRECOMPLETO` varchar(100) DEFAULT NULL,
  `CORREO` varchar(100) DEFAULT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  PRIMARY KEY (`IDCLIENTE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: ROLES
-- ============================================
DROP TABLE IF EXISTS `ROLES`;
CREATE TABLE `ROLES` (
  `IDROL` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIPCION` varchar(100) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  PRIMARY KEY (`IDROL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: USUARIOS
-- ============================================
DROP TABLE IF EXISTS `USUARIOS`;
CREATE TABLE `USUARIOS` (
  `IDUSUARIO` int(11) NOT NULL AUTO_INCREMENT,
  `DOCUMENTO` varchar(20) DEFAULT NULL,
  `NOMBRECOMPLETO` varchar(100) DEFAULT NULL,
  `CORREO` varchar(100) DEFAULT NULL,
  `CLAVE` varchar(100) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDROL` int(11) DEFAULT NULL,
  PRIMARY KEY (`IDUSUARIO`),
  KEY `FK_USUARIOS_ROLES` (`IDROL`),
  CONSTRAINT `FK_USUARIOS_ROLES` FOREIGN KEY (`IDROL`) REFERENCES `ROLES` (`IDROL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: PRODUCTOS
-- ============================================
DROP TABLE IF EXISTS `PRODUCTOS`;
CREATE TABLE `PRODUCTOS` (
  `IDPRODUCTO` int(11) NOT NULL AUTO_INCREMENT,
  `CODIGO` varchar(50) DEFAULT NULL,
  `NOMBRE` varchar(100) DEFAULT NULL,
  `DESCRIPCION` varchar(255) DEFAULT NULL,
  `STOCK` int(11) DEFAULT NULL,
  `PRECIOCOMPRA` decimal(10,2) DEFAULT NULL,
  `PRECIOVENTA` decimal(10,2) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDCATEGORIA` int(11) DEFAULT NULL,
  PRIMARY KEY (`IDPRODUCTO`),
  KEY `FK_PRODUCTOS_CATEGORIA` (`IDCATEGORIA`),
  CONSTRAINT `FK_PRODUCTOS_CATEGORIA` FOREIGN KEY (`IDCATEGORIA`) REFERENCES `CATEGORIAS` (`IDCATEGORIA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: PROVEEDORES
-- ============================================
DROP TABLE IF EXISTS `PROVEEDORES`;
CREATE TABLE `PROVEEDORES` (
  `IDPROVEEDOR` int(11) NOT NULL AUTO_INCREMENT,
  `DOCUMENTO` varchar(20) DEFAULT NULL,
  `RAZONSOCIAL` varchar(100) DEFAULT NULL,
  `CORREO` varchar(100) DEFAULT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  PRIMARY KEY (`IDPROVEEDOR`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: FORMAS_PAGO
-- ============================================
DROP TABLE IF EXISTS `FORMAS_PAGO`;
CREATE TABLE `FORMAS_PAGO` (
  `IDFORMAPAGO` int(11) NOT NULL AUTO_INCREMENT,
  `TIPOPAGO` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`IDFORMAPAGO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: COMPRAS
-- ============================================
DROP TABLE IF EXISTS `COMPRAS`;
CREATE TABLE `COMPRAS` (
  `IDCOMPRA` int(11) NOT NULL AUTO_INCREMENT,
  `TIPODOCUMENTO` varchar(50) DEFAULT NULL,
  `NUMERODOCUMENTO` varchar(50) DEFAULT NULL,
  `MONTOTOTAL` decimal(10,2) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDUSUARIO` int(11) DEFAULT NULL,
  `IDPROVEEDOR` int(11) DEFAULT NULL,
  `IDFORMAPAGO` int(11) DEFAULT NULL,
  PRIMARY KEY (`IDCOMPRA`),
  KEY `FK_COMPRAS_USUARIOS` (`IDUSUARIO`),
  KEY `FK_COMPRAS_PROVEEDORES` (`IDPROVEEDOR`),
  KEY `FK_COMPRAS_FORMAS` (`IDFORMAPAGO`),
  CONSTRAINT `FK_COMPRAS_FORMAS` FOREIGN KEY (`IDFORMAPAGO`) REFERENCES `FORMAS_PAGO` (`IDFORMAPAGO`),
  CONSTRAINT `FK_COMPRAS_PROVEEDORES` FOREIGN KEY (`IDPROVEEDOR`) REFERENCES `PROVEEDORES` (`IDPROVEEDOR`),
  CONSTRAINT `FK_COMPRAS_USUARIOS` FOREIGN KEY (`IDUSUARIO`) REFERENCES `USUARIOS` (`IDUSUARIO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: VENTAS
-- ============================================
DROP TABLE IF EXISTS `VENTAS`;
CREATE TABLE `VENTAS` (
  `IDVENTA` int(11) NOT NULL AUTO_INCREMENT,
  `TIPODOCUMENTO` varchar(50) DEFAULT NULL,
  `NUMERODOCUMENTO` varchar(50) DEFAULT NULL,
  `DOCUMENTOCLIENTE` varchar(20) DEFAULT NULL,
  `NOMBRECLIENTE` varchar(100) DEFAULT NULL,
  `MONTOPAGO` decimal(10,2) DEFAULT NULL,
  `MONTOCAMBIO` decimal(10,2) DEFAULT NULL,
  `MONTOTOTAL` decimal(10,2) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDUSUARIO` int(11) DEFAULT NULL,
  PRIMARY KEY (`IDVENTA`),
  KEY `FK_VENTAS_USUARIO` (`IDUSUARIO`),
  CONSTRAINT `FK_VENTAS_USUARIO` FOREIGN KEY (`IDUSUARIO`) REFERENCES `USUARIOS` (`IDUSUARIO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: DETALLECOMPRAS
-- ============================================
DROP TABLE IF EXISTS `DETALLECOMPRAS`;
CREATE TABLE `DETALLECOMPRAS` (
  `IDDETALLECOMPRA` int(11) NOT NULL AUTO_INCREMENT,
  `PRECIOVENTA` decimal(10,2) DEFAULT NULL,
  `PRECIOCOMPRA` decimal(10,2) DEFAULT NULL,
  `CANTIDAD` int(11) DEFAULT NULL,
  `MONTOTOTAL` decimal(10,2) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDCOMPRA` int(11) DEFAULT NULL,
  `IDPRODUCTO` int(11) DEFAULT NULL,
  PRIMARY KEY (`IDDETALLECOMPRA`),
  KEY `FK_DCOMPRA_COMPRA` (`IDCOMPRA`),
  KEY `FK_DCOMPRA_PRODUCTO` (`IDPRODUCTO`),
  CONSTRAINT `FK_DCOMPRA_COMPRA` FOREIGN KEY (`IDCOMPRA`) REFERENCES `COMPRAS` (`IDCOMPRA`),
  CONSTRAINT `FK_DCOMPRA_PRODUCTO` FOREIGN KEY (`IDPRODUCTO`) REFERENCES `PRODUCTOS` (`IDPRODUCTO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: DETALLEVENTAS
-- ============================================
DROP TABLE IF EXISTS `DETALLEVENTAS`;
CREATE TABLE `DETALLEVENTAS` (
  `IDDETALLEVENTA` int(11) NOT NULL AUTO_INCREMENT,
  `PRECIOVENTA` decimal(10,2) DEFAULT NULL,
  `CANTIDAD` int(11) DEFAULT NULL,
  `SUBTOTAL` decimal(10,2) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDVENTA` int(11) DEFAULT NULL,
  `IDPRODUCTO` int(11) DEFAULT NULL,
  PRIMARY KEY (`IDDETALLEVENTA`),
  KEY `FK_DVENTA_VENTA` (`IDVENTA`),
  KEY `FK_DVENTA_PRODUCTO` (`IDPRODUCTO`),
  CONSTRAINT `FK_DVENTA_PRODUCTO` FOREIGN KEY (`IDPRODUCTO`) REFERENCES `PRODUCTOS` (`IDPRODUCTO`),
  CONSTRAINT `FK_DVENTA_VENTA` FOREIGN KEY (`IDVENTA`) REFERENCES `VENTAS` (`IDVENTA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: PERMISOS
-- ============================================
DROP TABLE IF EXISTS `PERMISOS`;
CREATE TABLE `PERMISOS` (
  `IDPERMISO` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBREMENU` varchar(100) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDROL` int(11) DEFAULT NULL,
  PRIMARY KEY (`IDPERMISO`),
  KEY `FK_PERMISOS_ROLES` (`IDROL`),
  CONSTRAINT `FK_PERMISOS_ROLES` FOREIGN KEY (`IDROL`) REFERENCES `ROLES` (`IDROL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- INSERTAR DATOS DE PRUEBA
-- ============================================

-- Insertar roles
INSERT INTO `ROLES` (`DESCRIPCION`, `FECHAREGISTRO`) VALUES
('Administrador', NOW()),
('Empleado', NOW());

-- Insertar usuarios de prueba con contraseñas hasheadas
-- CONTRASEÑAS: admin123, empleado123, maria123
INSERT INTO `USUARIOS` (`DOCUMENTO`, `NOMBRECOMPLETO`, `CORREO`, `CLAVE`, `ESTADO`, `FECHAREGISTRO`, `IDROL`) VALUES
('12345678', 'Administrador Principal', 'admin@alisbook.com', '$2y$10$rKiHEWnN7B7yJ9Ft1PU/QuXCTVS2Z8Qe4FkCl7f9lJpZsE6WQOm4m', 'Activo', NOW(), 1),
('87654321', 'Juan Pérez', 'juan@alisbook.com', '$2y$10$7Qp3rL8vN9mK4xS6bF1eOuY2E3wR5tI7p0A9sD8fG6hJ4kL2mN5oP', 'Activo', NOW(), 2),
('11111111', 'María González', 'maria@alisbook.com', '$2y$10$8Rq4sM9wO0nL5yT7cG2fPvZ3F4xS6uJ8q1B0tE9gH7iK5lM3nO6pQ', 'Activo', NOW(), 2);

-- Insertar clientes de prueba
INSERT INTO `CLIENTES` (`DOCUMENTO`, `NOMBRECOMPLETO`, `CORREO`, `TELEFONO`, `ESTADO`, `FECHAREGISTRO`) VALUES
('11111111', 'María González', 'maria@email.com', '123456789', 'Activo', NOW()),
('22222222', 'Carlos López', 'carlos@email.com', '987654321', 'Activo', NOW());

-- Insertar productos de prueba
INSERT INTO `PRODUCTOS` (`CODIGO`, `NOMBRE`, `DESCRIPCION`, `STOCK`, `PRECIOCOMPRA`, `PRECIOVENTA`, `ESTADO`, `FECHAREGISTRO`, `IDCATEGORIA`) VALUES
('LIB001', 'Cien años de soledad', 'Obra maestra de Gabriel García Márquez', 15, 20.00, 25.99, 'Activo', NOW(), 1),
('LIB002', 'El Quijote', 'Clásico de Miguel de Cervantes', 10, 15.00, 19.99, 'Activo', NOW(), 1),
('LIB003', 'Sapiens', 'Una breve historia de la humanidad - Yuval Noah Harari', 8, 18.00, 22.50, 'Activo', NOW(), 1),
('UTL001', 'Cuaderno A4', 'Cuaderno universitario 100 hojas', 50, 2.50, 4.00, 'Activo', NOW(), 2),
('UTL002', 'Bolígrafo', 'Bolígrafo azul de tinta gel', 100, 0.50, 1.20, 'Activo', NOW(), 2);

-- Insertar formas de pago
INSERT INTO `FORMAS_PAGO` (`TIPOPAGO`) VALUES
('Efectivo'),
('Tarjeta de Crédito'),
('Tarjeta de Débito'),
('Transferencia');

-- ============================================
-- USUARIOS DE PRUEBA - CREDENCIALES
-- ============================================
-- 🔐 CREDENCIALES DE ACCESO:
-- 
-- 👤 ADMINISTRADOR:
--    📄 Documento: 12345678
--    🔑 Contraseña: admin123
--    👔 Rol: Administrador
--
-- 👤 EMPLEADO 1:
--    📄 Documento: 87654321  
--    🔑 Contraseña: empleado123
--    👔 Rol: Empleado
--
-- 👤 EMPLEADO 2:
--    📄 Documento: 11111111
--    🔑 Contraseña: maria123
--    👔 Rol: Empleado
--
-- ⚠️ NOTA: Las contraseñas están hasheadas con password_hash() de PHP
-- 🚀 Para configurar usuarios ejecutar: http://localhost/Presentacion-Interfaz/setup_users.php