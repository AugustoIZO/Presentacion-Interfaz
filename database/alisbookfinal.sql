-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-02-2026 a las 00:34:49
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
(8, '46038933', 'augusto', '', '', 'Activo', '2026-02-20 20:16:22'),
(9, '46130457', 'Jero babsia', '', '', 'Activo', '2026-02-21 15:54:15'),
(10, '332323', 'prueba cancelar', '', '', 'Activo', '2026-02-21 23:41:56'),
(11, '11', 'augusto prueba', '', '', 'Activo', '2026-02-24 20:20:42');

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
(15, 'Factura', 'C-000001', 500000.00, '2026-02-20 20:15:30', 4, 6, 3),
(16, 'Factura', 'C-000002', 1250000.00, '2026-02-21 15:52:34', 13, 7, 3),
(17, 'Factura', 'C-000003', 1200000.00, '2026-02-24 20:20:00', 13, 6, 2);

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
(29, 20000.00, 10000.00, 50, 500000.00, NULL, '2026-02-20 20:15:30', 15, 19),
(30, 35000.00, 25000.00, 50, 1250000.00, NULL, '2026-02-21 15:52:34', 16, 20),
(31, 35000.00, 12000.00, 100, 1200000.00, NULL, '2026-02-24 20:20:00', 17, 21);

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
(22, 20000.00, 30, 600000.00, '2026-02-20 20:16:22', 12, 19),
(23, 20000.00, 2, 40000.00, '2026-02-21 15:54:15', 13, 19),
(24, 35000.00, 1, 35000.00, '2026-02-21 15:54:15', 13, 20),
(25, 20000.00, 2, 40000.00, '2026-02-21 15:54:45', 14, 19),
(26, 35000.00, 1, 35000.00, '2026-02-21 15:54:45', 14, 20),
(27, 20000.00, 1, 20000.00, '2026-02-21 23:41:56', 15, 19),
(28, 20000.00, 2, 40000.00, '2026-02-24 20:20:42', 16, 19),
(29, 35000.00, 2, 70000.00, '2026-02-24 20:20:42', 16, 20),
(30, 35000.00, 2, 70000.00, '2026-02-24 20:20:42', 16, 21);

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
(19, NULL, 'Cavani', 'Producto agregado desde compra', 13, 10000.00, 20000.00, 'Activo', '2026-02-20 20:15:30', 1),
(20, NULL, 'El jero y su pandilla', 'Producto agregado desde compra', 46, 25000.00, 35000.00, 'Activo', '2026-02-21 15:52:34', 1),
(21, NULL, 'Libro harry potter', 'Producto agregado desde compra', 98, 12000.00, 35000.00, 'Activo', '2026-02-24 20:20:00', 1);

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
(6, '8080', 'Editorial facha', 'facha@gmail.com', '341', 'Activo', '2026-02-20 20:14:18'),
(7, '4444444', 'Editorial jero animal', 'jero@gmail.com', '3414444444', 'Activo', '2026-02-21 15:51:41');

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
(4, '12345678', 'Administrador Principal', 'admin@alisbook.com', '$2y$10$G3QTafeU1LdRye7/3e3.TOdQb4jA/ZBSa.GeW.GyYk1tKxpWYgijG', 'Activo', '2025-10-02 17:13:47', 1),
(13, '46038933', 'AUGUSTO', 'augustoiocco8@gmail.com', '$2y$10$U9invJ3kgw9hliQEKgolo.nl9HQvmisRFXmssricWjXF4snot42XC', 'Activo', '2026-02-20 20:21:54', 2);

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
(12, 'Factura', 'V-000001', '46038933', 'augusto', 600000.00, 0.00, 600000.00, '2026-02-20 20:16:22', 4),
(13, 'Factura', 'V-000002', '46130457', 'Jero babsia', 75000.00, 0.00, 75000.00, '2026-02-21 15:54:15', 13),
(14, 'Factura', 'V-000003', '46130457', 'Jero babsia', 75000.00, 0.00, 75000.00, '2026-02-21 15:54:45', 13),
(15, 'Factura', 'V-000004', '332323', 'prueba cancelar', 20000.00, 0.00, 20000.00, '2026-02-21 23:41:56', 13),
(16, 'Factura', 'V-000005', '11', 'augusto prueba', 180000.00, 0.00, 180000.00, '2026-02-24 20:20:42', 13);

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
  MODIFY `IDCLIENTE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `IDCOMPRA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `detallecompras`
--
ALTER TABLE `detallecompras`
  MODIFY `IDDETALLECOMPRA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `detalleventas`
--
ALTER TABLE `detalleventas`
  MODIFY `IDDETALLEVENTA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
  MODIFY `IDPRODUCTO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `IDPROVEEDOR` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `IDROL` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `IDUSUARIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `IDVENTA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
