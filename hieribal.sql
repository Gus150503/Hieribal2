-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-11-2025 a las 02:03:41
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
-- Base de datos: `hieribal`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`cantidad` * `precio`) STORED,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_producto`, `nombre_producto`, `cantidad`, `precio`, `fecha_agregado`, `id_cliente`) VALUES
(1002, 3, 'Menta', 2, 1200.00, '2025-08-28 14:16:55', NULL),
(3001, 2, 'Eucalipto', 4, 2000.00, '2025-08-28 14:27:37', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Activo',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `verificado` tinyint(1) NOT NULL DEFAULT 0,
  `token_verificacion` varchar(64) DEFAULT NULL,
  `token_recuperacion` varchar(64) DEFAULT NULL,
  `recuperacion_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `cedula`, `nombres`, `apellidos`, `telefono`, `correo`, `contraseña`, `estado`, `fecha_registro`, `verificado`, `token_verificacion`, `token_recuperacion`, `recuperacion_expira`) VALUES
(41, '1000789324', 'Gustavo', 'Cuevas', '3145909865', 'gustavoalexiscuevas@gmail.com', '$2y$10$uW8EN8Qg4u0Tss9DsmThIeM3y/pIDK8AV7NvK0D3MFRnpFEH0j2Wy', 'Activo', '2025-11-10 20:27:11', 1, NULL, NULL, NULL),
(43, '1223123123', 'jaiderstivenson', 'Pineda', '', 'jaiderpineda2003@gmail.com', '$2y$10$FB6kq7rjHjZCG5ctdnLxweFZgbB9aqbLGQFvGsQn9qZdlIjFGaWn2', 'Activo', '2025-11-12 18:16:18', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config`
--

CREATE TABLE `config` (
  `clave` varchar(80) NOT NULL,
  `valor` text NOT NULL,
  `tipo` enum('str','int','bool','json') DEFAULT 'str',
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `config`
--

INSERT INTO `config` (`clave`, `valor`, `tipo`, `actualizado_en`) VALUES
('correo_activo', '0', 'bool', '2025-09-16 01:36:33'),
('correo_from', '', 'str', '2025-09-16 01:36:33'),
('correo_host', '', 'str', '2025-09-16 01:36:33'),
('correo_puerto', '587', 'int', '2025-09-16 01:36:33'),
('correo_seguridad', 'tls', 'str', '2025-09-16 01:36:33'),
('correo_usuario', '', 'str', '2025-09-16 01:36:33'),
('empresa_direccion', '', 'str', '2025-09-16 01:36:33'),
('empresa_nombre', 'Mi Hieribal', 'str', '2025-09-18 01:09:23'),
('empresa_ruc', '', 'str', '2025-09-16 01:36:33'),
('ui_color_principal', '#00ff80', 'str', '2025-10-16 00:30:13'),
('ui_tema', 'light', 'str', '2025-10-16 23:38:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_pedido`
--

CREATE TABLE `historial_pedido` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `metodo_pago` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `codigo_interno` varchar(40) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0,
  `stock_maximo` int(11) DEFAULT 0,
  `punto_reorden` int(11) DEFAULT 0,
  `ubicacion` varchar(100) DEFAULT NULL,
  `estado` enum('disponible','agotado','pendiente') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id`, `id_producto`, `codigo_interno`, `stock`, `stock_minimo`, `stock_maximo`, `punto_reorden`, `ubicacion`, `estado`) VALUES
(16, 2, NULL, 40, 0, 0, 0, NULL, 'disponible'),
(17, 3, NULL, 12, 0, 0, 0, NULL, 'disponible'),
(18, 4, NULL, 15, 0, 0, 0, NULL, 'disponible'),
(19, 5, NULL, 10, 0, 0, 0, NULL, 'disponible'),
(20, 6, '555', 10, 77, 77, 9, 'calll', 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `categoria` varchar(60) DEFAULT NULL,
  `marca` varchar(60) DEFAULT NULL,
  `presentacion` varchar(60) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `stock_actual` varchar(20) DEFAULT NULL,
  `stock_minimo` int(10) UNSIGNED DEFAULT NULL,
  `lote` varchar(40) DEFAULT NULL,
  `f_vencimiento` date DEFAULT NULL,
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `iva` decimal(5,2) DEFAULT 0.00,
  `codigo_sku` varchar(40) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `imagen` varchar(255) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `categoria`, `marca`, `presentacion`, `descripcion`, `stock_actual`, `stock_minimo`, `lote`, `f_vencimiento`, `precio_compra`, `precio_venta`, `iva`, `codigo_sku`, `ubicacion`, `estado`, `imagen`, `creado`) VALUES
(2, 'Eucalipto', 'ramas', 'no se', 'fisica', '50', 40, 'eucalipto', '2', '2025-11-22', 1000.00, 2000.00, 0.00, '5342', 'ni idea', 'activo', 'http://localhost/Hieribal2/public/assets/img/prod_6918aa34e0270.png', '2025-08-27 23:37:31'),
(3, 'Menta', NULL, NULL, NULL, '3', 12, NULL, NULL, NULL, NULL, 1200.00, 0.00, NULL, NULL, 'activo', 'menta.png\n', '2025-08-28 14:14:42'),
(4, 'Toronjil', NULL, NULL, NULL, '4', 15, NULL, NULL, NULL, NULL, 1100.00, 0.00, NULL, NULL, 'activo', 'toronjil.png\n', '2025-08-28 14:15:14'),
(5, 'Manzanilla', NULL, NULL, NULL, '0', 5, NULL, NULL, NULL, NULL, 1500.00, 0.00, NULL, NULL, 'activo', 'manzanilla.png\n', '2025-08-28 14:15:56'),
(6, 'Diente de leon', NULL, NULL, NULL, '0', 5, NULL, NULL, NULL, NULL, 1500.00, 0.00, NULL, NULL, 'activo', 'leon.png', '2025-08-28 14:15:56');

--
-- Disparadores `productos`
--
DELIMITER $$
CREATE TRIGGER `productos_ai` AFTER INSERT ON `productos` FOR EACH ROW BEGIN
  INSERT INTO inventario (id_producto, stock) VALUES (NEW.id, 0);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `empresa` varchar(120) DEFAULT NULL,
  `nit` varchar(30) DEFAULT NULL,
  `nombre_contacto` varchar(120) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `ciudad` varchar(60) DEFAULT NULL,
  `condiciones_pago` varchar(100) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `empresa`, `nit`, `nombre_contacto`, `telefono`, `email`, `direccion`, `ciudad`, `condiciones_pago`, `estado`, `creado`) VALUES
(3, 'ni ideaq', '123445', 'jmm', '3123214122', 'niidea@gmail.com', 'calle 80', 'bogota', 'tarjeta', 'activo', '2025-10-24 23:12:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte_venta`
--

CREATE TABLE `reporte_venta` (
  `id` int(11) NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `vendedor_id` int(11) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `fecha` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reporte_venta`
--

INSERT INTO `reporte_venta` (`id`, `numero_factura`, `producto_id`, `cantidad`, `precio`, `total`, `cliente_id`, `vendedor_id`, `metodo_pago`, `fecha`, `observaciones`, `created_at`) VALUES
(2, '2', 3, 56, 23.00, 1288.00, 43, 41, 'tarjeta', '2025-11-12', 'popocho', '2025-11-15 16:26:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('Admin','Empleado','Cajero') NOT NULL DEFAULT 'Empleado',
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `correo_verificado` tinyint(1) NOT NULL DEFAULT 0,
  `correo_verificacion_token` varchar(64) DEFAULT NULL,
  `correo_verificacion_expira` datetime DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') NOT NULL,
  `token_recuperacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `password`, `rol`, `nombres`, `apellidos`, `correo`, `correo_verificado`, `correo_verificacion_token`, `correo_verificacion_expira`, `fecha_creacion`, `estado`, `token_recuperacion`) VALUES
(41, 'Cajero', '$2y$10$r933abSbmZeZEPTPrCiCu.Aao/yyF/YstmCVAmhj./o30bQk2MS3G', 'Cajero', 'Juan Pepito', 'Martinez Hernandez', 'gustavoalexis@gmail.com', 0, '9fc34c171c710ac435f2928dc91dc195', '2025-11-20 19:15:22', '2025-11-13 20:40:29', 'Activo', NULL),
(48, 'Admin2', '$2y$10$Lu2Vp7ehIGMrgCoFGYRQReRj7Yg3V9PFArO5lNlzKdIACJr2IrzFK', 'Admin', 'Gustavo', 'Cuevas', 'gustavoalexiscuevas@gmail.com', 1, NULL, NULL, '2025-11-19 19:44:44', 'Activo', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `metodo_pago` varchar(50) DEFAULT 'Efectivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `id_carrito`, `total`, `fecha_venta`, `metodo_pago`) VALUES
(5002, 3001, 8000.00, '2025-08-28 14:27:37', 'efectivo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `historial_pedido`
--
ALTER TABLE `historial_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_inv_producto` (`id_producto`),
  ADD KEY `producto_id` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reporte_venta`
--
ALTER TABLE `reporte_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reporte_producto` (`producto_id`),
  ADD KEY `fk_reporte_cliente` (`cliente_id`),
  ADD KEY `fk_reporte_vendedor` (`vendedor_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuarios_usuario` (`usuario`),
  ADD UNIQUE KEY `uq_usuarios_correo` (`correo`),
  ADD UNIQUE KEY `uq_correo` (`correo`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `fk_carrito` (`id_carrito`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3002;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `historial_pedido`
--
ALTER TABLE `historial_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reporte_venta`
--
ALTER TABLE `reporte_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5003;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial_pedido`
--
ALTER TABLE `historial_pedido`
  ADD CONSTRAINT `historial_pedido_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `fk_inv_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reporte_venta`
--
ALTER TABLE `reporte_venta`
  ADD CONSTRAINT `fk_reporte_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_reporte_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `fk_reporte_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_carrito` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
