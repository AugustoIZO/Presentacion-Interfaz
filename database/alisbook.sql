-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-10-2025 a las 21:43:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `alisbook`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `IDCATEGORIA` int(11) NOT NULL,
  `DESCRIPCION` varchar(100) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`IDCATEGORIA`, `DESCRIPCION`, `ESTADO`, `FECHAREGISTRO`) VALUES
(1, 'Libros', 'Activo', '2025-08-12 08:10:18'),
(2, 'Utiles', 'Activo', '2025-08-12 08:10:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `IDCLIENTE` int(11) NOT NULL,
  `DOCUMENTO` varchar(20) DEFAULT NULL,
  `NOMBRECOMPLETO` varchar(100) DEFAULT NULL,
  `CORREO` varchar(100) DEFAULT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`IDCLIENTE`, `DOCUMENTO`, `NOMBRECOMPLETO`, `CORREO`, `TELEFONO`, `ESTADO`, `FECHAREGISTRO`) VALUES
(1, '11111111', 'María González', 'maria@email.com', '123456789', 'Activo', '2025-10-02 17:12:41'),
(2, '22222222', 'Carlos López', 'carlos@email.com', '987654321', 'Activo', '2025-10-02 17:12:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `IDCOMPRA` int(11) NOT NULL,
  `TIPODOCUMENTO` varchar(50) DEFAULT NULL,
  `NUMERODOCUMENTO` varchar(50) DEFAULT NULL,
  `MONTOTOTAL` decimal(10,2) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDUSUARIO` int(11) DEFAULT NULL,
  `IDPROVEEDOR` int(11) DEFAULT NULL,
  `IDFORMAPAGO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`IDCOMPRA`, `TIPODOCUMENTO`, `NUMERODOCUMENTO`, `MONTOTOTAL`, `FECHAREGISTRO`, `IDUSUARIO`, `IDPROVEEDOR`, `IDFORMAPAGO`) VALUES
(1, 'Factura', 'F001-00001', 1250.50, '2025-10-01 09:30:00', 4, 1, 1),
(2, 'Boleta', 'B001-00001', 850.75, '2025-10-01 14:20:00', 5, 2, 2),
(3, 'Factura', 'F001-00002', 2150.00, '2025-10-02 10:15:00', 4, 3, 3),
(4, 'Factura', 'F001-00003', 675.25, '2025-10-02 16:45:00', 6, 4, 1),
(5, 'Boleta', 'B001-00002', 1890.80, '2025-10-03 11:00:00', 5, 5, 4),
(6, 'Factura', 'F001-00004', 3200.90, '2025-10-03 15:30:00', 4, 1, 2),
(7, 'Boleta', 'B001-00003', 950.40, '2025-10-03 17:20:00', 6, 2, 1),
(8, 'Factura', '11111', 305.00, '2025-10-04 16:04:15', 4, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallecompras`
--

CREATE TABLE `detallecompras` (
  `IDDETALLECOMPRA` int(11) NOT NULL,
  `PRECIOVENTA` decimal(10,2) DEFAULT NULL,
  `PRECIOCOMPRA` decimal(10,2) DEFAULT NULL,
  `CANTIDAD` int(11) DEFAULT NULL,
  `MONTOTOTAL` decimal(10,2) DEFAULT NULL,
  `DESCRIPCION` varchar(255) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDCOMPRA` int(11) DEFAULT NULL,
  `IDPRODUCTO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detallecompras`
--

INSERT INTO `detallecompras` (`IDDETALLECOMPRA`, `PRECIOVENTA`, `PRECIOCOMPRA`, `CANTIDAD`, `MONTOTOTAL`, `DESCRIPCION`, `FECHAREGISTRO`, `IDCOMPRA`, `IDPRODUCTO`) VALUES
(1, 25.99, 20.00, 50, 1000.00, 'Libros clásicos de literatura - Edición especial tapa dura', '2025-10-01 09:30:00', 1, 1),
(2, 22.50, 18.00, 10, 180.00, 'Libros de historia contemporánea - Formato estándar', '2025-10-01 09:30:00', 1, 3),
(3, 1.20, 0.50, 150, 75.00, 'Bolígrafos azules de tinta gel - Paquete institucional', '2025-10-01 09:30:00', 1, 5),
(4, 19.99, 15.00, 30, 450.00, 'Clásicos de la literatura española - Nueva edición', '2025-10-01 14:20:00', 2, 2),
(5, 4.00, 2.50, 100, 250.00, 'Cuadernos universitarios A4 - 100 hojas rayadas', '2025-10-01 14:20:00', 2, 4),
(6, 1.20, 0.50, 250, 125.00, 'Bolígrafos de colores variados - Material escolar', '2025-10-01 14:20:00', 2, 5),
(7, 25.99, 20.00, 80, 1600.00, 'Literatura contemporánea - Colección completa', '2025-10-02 10:15:00', 3, 1),
(8, 22.50, 18.00, 25, 450.00, 'Libros de ciencias sociales - Edición académica', '2025-10-02 10:15:00', 3, 3),
(9, 4.00, 2.50, 25, 62.50, 'Cuadernos de apuntes A4 - Material de oficina', '2025-10-02 16:45:00', 4, 4),
(10, 1.20, 0.50, 600, 300.00, 'Bolígrafos negros y azules - Compra mayorista', '2025-10-02 16:45:00', 4, 5),
(11, 18.99, 12.50, 75, 937.50, 'Libros técnicos especializados - Nueva colección', '2025-10-03 11:00:00', 5, 8),
(12, 25.00, 15.00, 40, 600.00, 'Literatura moderna - Edición de lujo', '2025-10-03 11:00:00', 5, 9),
(13, 4.00, 2.50, 85, 212.50, 'Material de escritorio - Cuadernos profesionales', '2025-10-03 11:00:00', 5, 4),
(14, 25.99, 20.00, 120, 2400.00, 'Gran compra de literatura clásica - Stock anual', '2025-10-03 15:30:00', 6, 1),
(15, 22.50, 18.00, 45, 810.00, 'Libros académicos especializados - Edición universitaria', '2025-10-03 17:20:00', 7, 3),
(16, 1.20, 0.50, 10, 5.00, NULL, '2025-10-04 16:04:15', 8, 5),
(17, 4.00, 2.50, 10, 25.00, NULL, '2025-10-04 16:04:15', 8, 4),
(18, 18.99, 12.50, 10, 125.00, NULL, '2025-10-04 16:04:15', 8, 8),
(19, 25.00, 15.00, 10, 150.00, NULL, '2025-10-04 16:04:15', 8, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalleventas`
--

CREATE TABLE `detalleventas` (
  `IDDETALLEVENTA` int(11) NOT NULL,
  `PRECIOVENTA` decimal(10,2) DEFAULT NULL,
  `CANTIDAD` int(11) DEFAULT NULL,
  `SUBTOTAL` decimal(10,2) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDVENTA` int(11) DEFAULT NULL,
  `IDPRODUCTO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalleventas`
--

INSERT INTO `detalleventas` (`IDDETALLEVENTA`, `PRECIOVENTA`, `CANTIDAD`, `SUBTOTAL`, `FECHAREGISTRO`, `IDVENTA`, `IDPRODUCTO`) VALUES
(1, 1.20, 20, 24.00, '2025-10-02 17:26:00', 1, 5),
(2, 1.20, 2, 2.40, '2025-10-04 15:55:14', 4, 5),
(3, 4.00, 2, 8.00, '2025-10-04 15:55:14', 4, 4),
(4, 1.20, 50, 60.00, '2025-10-04 16:05:20', 5, 5),
(5, 4.00, 50, 200.00, '2025-10-04 16:05:20', 5, 4),
(6, 18.99, 20, 379.80, '2025-10-04 16:05:20', 5, 8),
(7, 25.00, 20, 500.00, '2025-10-04 16:05:20', 5, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formas_pago`
--

CREATE TABLE `formas_pago` (
  `IDFORMAPAGO` int(11) NOT NULL,
  `TIPOPAGO` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `formas_pago`
--

INSERT INTO `formas_pago` (`IDFORMAPAGO`, `TIPOPAGO`) VALUES
(1, 'Efectivo'),
(2, 'Tarjeta de Crédito'),
(3, 'Tarjeta de Débito'),
(4, 'Transferencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `IDPERMISO` int(11) NOT NULL,
  `NOMBREMENU` varchar(100) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDROL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `IDPRODUCTO` int(11) NOT NULL,
  `CODIGO` varchar(50) DEFAULT NULL,
  `NOMBRE` varchar(100) DEFAULT NULL,
  `DESCRIPCION` varchar(255) DEFAULT NULL,
  `STOCK` int(11) DEFAULT NULL,
  `PRECIOCOMPRA` decimal(10,2) DEFAULT NULL,
  `PRECIOVENTA` decimal(10,2) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDCATEGORIA` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`IDPRODUCTO`, `CODIGO`, `NOMBRE`, `DESCRIPCION`, `STOCK`, `PRECIOCOMPRA`, `PRECIOVENTA`, `ESTADO`, `FECHAREGISTRO`, `IDCATEGORIA`) VALUES
(1, 'LIB001', 'Cien años de soledad', 'Obra maestra de Gabriel García Márquez', 15, 20.00, 25.99, 'Inactivo', '2025-10-02 17:12:41', 1),
(2, 'LIB002', 'El Quijote', 'Clásico de Miguel de Cervantes', 10, 15.00, 19.99, 'Inactivo', '2025-10-02 17:12:41', 1),
(3, 'LIB003', 'Sapiens', 'Una breve historia de la humanidad - Yuval Noah Harari', 8, 18.00, 22.50, 'Activo', '2025-10-02 17:12:41', 1),
(4, 'UTL001', 'Cuaderno A4', 'Cuaderno universitario 100 hojas', 8, 2.50, 4.00, 'Activo', '2025-10-02 17:12:41', 2),
(5, 'UTL002', 'Bolígrafo', 'Bolígrafo azul de tinta gel', 38, 0.50, 1.20, 'Activo', '2025-10-02 17:12:41', 2),
(6, '', 'Tito la champion liga', 'jojoj', 10, 70.00, 900.00, 'Activo', '2025-10-02 20:45:37', 1),
(7, 'TEST001', 'Libro Test API', 'Libro para probar API corregida', 5, 10.00, 15.99, 'Activo', '2025-10-03 00:13:09', 1),
(8, 'TESTFIX001', 'Libro Test Corregido', 'Libro con estructura correcta', 0, 12.50, 18.99, 'Activo', '2025-10-03 00:14:19', 1),
(9, 'LIB999', 'Nuevo Libro', 'Descripción del libro', 0, 15.00, 25.00, 'Activo', '2025-10-03 00:16:18', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `IDPROVEEDOR` int(11) NOT NULL,
  `DOCUMENTO` varchar(20) DEFAULT NULL,
  `RAZONSOCIAL` varchar(100) DEFAULT NULL,
  `CORREO` varchar(100) DEFAULT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`IDPROVEEDOR`, `DOCUMENTO`, `RAZONSOCIAL`, `CORREO`, `TELEFONO`, `ESTADO`, `FECHAREGISTRO`) VALUES
(1, '20123456789', 'Editorial Santillana S.A.', 'ventas@santillana.com', '01-234-5678', 'Activo', '2025-10-03 10:00:00'),
(2, '20987654321', 'Distribuidora Norma Ltda.', 'contacto@norma.com', '01-876-5432', 'Activo', '2025-10-03 10:15:00'),
(3, '20456789123', 'Librería Pedagógica S.R.L.', 'pedidos@pedagogica.com', '01-555-1234', 'Activo', '2025-10-03 10:30:00'),
(4, '20321654987', 'Papelería Universal S.A.C.', 'compras@universal.com', '01-777-8899', 'Activo', '2025-10-03 10:45:00'),
(5, '20654321789', 'Ediciones SM Perú', 'info@sm.com.pe', '01-333-4567', 'Activo', '2025-10-03 11:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `IDROL` int(11) NOT NULL,
  `DESCRIPCION` varchar(100) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`IDROL`, `DESCRIPCION`, `FECHAREGISTRO`) VALUES
(1, 'Administrador', '2025-10-02 17:12:41'),
(2, 'Empleado', '2025-10-02 17:12:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `IDUSUARIO` int(11) NOT NULL,
  `DOCUMENTO` varchar(20) DEFAULT NULL,
  `NOMBRECOMPLETO` varchar(100) DEFAULT NULL,
  `CORREO` varchar(100) DEFAULT NULL,
  `CLAVE` varchar(100) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDROL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`IDUSUARIO`, `DOCUMENTO`, `NOMBRECOMPLETO`, `CORREO`, `CLAVE`, `ESTADO`, `FECHAREGISTRO`, `IDROL`) VALUES
(4, '12345678', 'Administrador Principal', 'admin@alisbook.com', '$2y$10$dQN6s9MtIHFrhp8T0o6R3.ssHG9rO8OMLPryQ2g7oiECLL9celmXO', 'Activo', '2025-10-02 17:13:47', 1),
(5, '87654321', 'Juan Pérez', 'juan@alisbook.com', '$2y$10$X3XEbe5UsUp8jibQHy/Tquar4GXHFKNFvWxazjn.QoreV9COfkffC', 'Activo', '2025-10-02 17:13:47', 2),
(6, '11111111', 'María González', 'maria@alisbook.com', '$2y$10$sTcl5PyuMSBVMRhF2UeXVuIOLGx6Ut1/PUQskX3pRRnfdlBrqrV5a', 'Activo', '2025-10-02 17:13:47', 2),
(7, '99999999', 'Usuario Test API', 'test@api.com', 'test123', 'Activo', NULL, 2),
(8, '88888888', 'Usuario API Corregido', 'corregido@api.com', 'password123', 'Activo', NULL, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `IDVENTA` int(11) NOT NULL,
  `TIPODOCUMENTO` varchar(50) DEFAULT NULL,
  `NUMERODOCUMENTO` varchar(50) DEFAULT NULL,
  `DOCUMENTOCLIENTE` varchar(20) DEFAULT NULL,
  `NOMBRECLIENTE` varchar(100) DEFAULT NULL,
  `MONTOPAGO` decimal(10,2) DEFAULT NULL,
  `MONTOCAMBIO` decimal(10,2) DEFAULT NULL,
  `MONTOTOTAL` decimal(10,2) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `IDUSUARIO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`IDVENTA`, `TIPODOCUMENTO`, `NUMERODOCUMENTO`, `DOCUMENTOCLIENTE`, `NOMBRECLIENTE`, `MONTOPAGO`, `MONTOCAMBIO`, `MONTOTOTAL`, `FECHAREGISTRO`, `IDUSUARIO`) VALUES
(1, 'Factura', '11111', '46038933', 'Tito', 24.00, 0.00, 24.00, '2025-10-02 17:26:00', 5),
(3, 'BOLETA', 'B001-003156', '', 'Cliente Test Corregido', 80.00, 4.50, 75.50, '2025-10-03 00:15:03', 4),
(4, 'Boleta', '1231233', '46038933', 'guacho', 10.40, 0.00, 10.40, '2025-10-04 15:55:14', 4),
(5, 'Boleta', '88888', '202022020', 'YOOO', 2500.00, 1360.20, 1139.80, '2025-10-04 16:05:20', 4);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`IDCATEGORIA`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`IDCLIENTE`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`IDCOMPRA`),
  ADD KEY `FK_COMPRAS_USUARIOS` (`IDUSUARIO`),
  ADD KEY `FK_COMPRAS_PROVEEDORES` (`IDPROVEEDOR`),
  ADD KEY `FK_COMPRAS_FORMAS` (`IDFORMAPAGO`);

--
-- Indices de la tabla `detallecompras`
--
ALTER TABLE `detallecompras`
  ADD PRIMARY KEY (`IDDETALLECOMPRA`),
  ADD KEY `FK_DCOMPRA_COMPRA` (`IDCOMPRA`),
  ADD KEY `FK_DCOMPRA_PRODUCTO` (`IDPRODUCTO`);

--
-- Indices de la tabla `detalleventas`
--
ALTER TABLE `detalleventas`
  ADD PRIMARY KEY (`IDDETALLEVENTA`),
  ADD KEY `FK_DVENTA_VENTA` (`IDVENTA`),
  ADD KEY `FK_DVENTA_PRODUCTO` (`IDPRODUCTO`);

--
-- Indices de la tabla `formas_pago`
--
ALTER TABLE `formas_pago`
  ADD PRIMARY KEY (`IDFORMAPAGO`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`IDPERMISO`),
  ADD KEY `FK_PERMISOS_ROLES` (`IDROL`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`IDPRODUCTO`),
  ADD KEY `FK_PRODUCTOS_CATEGORIA` (`IDCATEGORIA`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`IDPROVEEDOR`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`IDROL`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`IDUSUARIO`),
  ADD KEY `FK_USUARIOS_ROLES` (`IDROL`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`IDVENTA`),
  ADD KEY `FK_VENTAS_USUARIO` (`IDUSUARIO`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `IDCATEGORIA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `IDCLIENTE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `IDCOMPRA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `detallecompras`
--
ALTER TABLE `detallecompras`
  MODIFY `IDDETALLECOMPRA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `detalleventas`
--
ALTER TABLE `detalleventas`
  MODIFY `IDDETALLEVENTA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `formas_pago`
--
ALTER TABLE `formas_pago`
  MODIFY `IDFORMAPAGO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `IDPERMISO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `IDPRODUCTO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `IDPROVEEDOR` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `IDROL` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `IDUSUARIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `IDVENTA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `FK_COMPRAS_FORMAS` FOREIGN KEY (`IDFORMAPAGO`) REFERENCES `formas_pago` (`IDFORMAPAGO`),
  ADD CONSTRAINT `FK_COMPRAS_PROVEEDORES` FOREIGN KEY (`IDPROVEEDOR`) REFERENCES `proveedores` (`IDPROVEEDOR`),
  ADD CONSTRAINT `FK_COMPRAS_USUARIOS` FOREIGN KEY (`IDUSUARIO`) REFERENCES `usuarios` (`IDUSUARIO`);

--
-- Filtros para la tabla `detallecompras`
--
ALTER TABLE `detallecompras`
  ADD CONSTRAINT `FK_DCOMPRA_COMPRA` FOREIGN KEY (`IDCOMPRA`) REFERENCES `compras` (`IDCOMPRA`),
  ADD CONSTRAINT `FK_DCOMPRA_PRODUCTO` FOREIGN KEY (`IDPRODUCTO`) REFERENCES `productos` (`IDPRODUCTO`);

--
-- Filtros para la tabla `detalleventas`
--
ALTER TABLE `detalleventas`
  ADD CONSTRAINT `FK_DVENTA_PRODUCTO` FOREIGN KEY (`IDPRODUCTO`) REFERENCES `productos` (`IDPRODUCTO`),
  ADD CONSTRAINT `FK_DVENTA_VENTA` FOREIGN KEY (`IDVENTA`) REFERENCES `ventas` (`IDVENTA`);

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `FK_PERMISOS_ROLES` FOREIGN KEY (`IDROL`) REFERENCES `roles` (`IDROL`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `FK_PRODUCTOS_CATEGORIA` FOREIGN KEY (`IDCATEGORIA`) REFERENCES `categorias` (`IDCATEGORIA`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `FK_USUARIOS_ROLES` FOREIGN KEY (`IDROL`) REFERENCES `roles` (`IDROL`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `FK_VENTAS_USUARIO` FOREIGN KEY (`IDUSUARIO`) REFERENCES `usuarios` (`IDUSUARIO`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
