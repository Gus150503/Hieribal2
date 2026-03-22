-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql100.byethost3.com
-- Tiempo de generación: 23-02-2026 a las 19:27:16
-- Versión del servidor: 11.4.10-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `b3_40603224_hieribal`
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
  `id_cliente` int(11) DEFAULT NULL,
  `telefono_envio` varchar(20) DEFAULT NULL,
  `direccion_envio` varchar(255) DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_producto`, `nombre_producto`, `cantidad`, `precio`, `fecha_agregado`, `id_cliente`, `telefono_envio`, `direccion_envio`, `metodo_pago`, `notas`) VALUES
(3008, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 19:27:03', 45, '3145970986', 'calle 80 bis sur n 94- 21', 'Transferencia bancaria', ''),
(3009, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 19:51:00', 45, '3145970986', 'calle 80', 'Contra entrega', ''),
(3010, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:00:29', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3011, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:24', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3012, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:24', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3013, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:24', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3014, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:24', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3015, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:32', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3016, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:32', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3017, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:32', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3018, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:32', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3019, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:03:32', 45, '3145970986', 'calle 80', 'Nequi', ''),
(3020, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:04:23', 45, '3145970986', 'calle 80', 'Contra entrega', ''),
(3021, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:05:25', 45, '3145970986', 'calle 80', 'Transferencia bancaria', ''),
(3022, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-05 20:27:26', 49, '3132254044', 'Calle71 sur ·87-10', 'Contra entrega', ''),
(3023, 2, 'Proteína', 1, '100000.00', '2025-12-05 20:28:20', 49, '3132254044', 'Calle71 sur ·87-10', 'Nequi', ''),
(3024, 3, 'Menta', 1, '1200.00', '2025-12-05 23:24:25', 45, '3321312322', 'cll 80 bis sur #94-21', 'Nequi', ''),
(3025, 23, 'Aceite de té de arbol', 1, '16000.00', '2025-12-13 17:25:46', 49, '3132254044', 'Calle71 sur ·87-10', 'Contra entrega', 'Tener cuidado con los productos'),
(3026, 17, 'Proteína de fresa', 1, '250000.00', '2025-12-13 17:25:46', 49, '3132254044', 'Calle71 sur ·87-10', 'Contra entrega', 'Tener cuidado con los productos'),
(3027, 24, 'Aceite de oliva', 1, '18000.00', '2025-12-13 17:45:29', 49, '3132254044', 'Calle71 sur ·87-10', 'Contra entrega', 'Tener cuidado con los productos'),
(3028, 23, 'Aceite de té de arbol', 1, '16000.00', '2026-02-14 01:56:06', 45, '4523423423', '23423423', 'Contra entrega', '42424');

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
(44, '1023123123', 'michel', 'lugo', '2342424242', 'michel18lugo@gmail.com', '$2y$10$pACGlDidPyehtfL1wNNOJuXS8z808D8Uq7WXSxkgIEJrlcP20HXKK', 'Activo', '2025-11-29 08:13:34', 0, '9ed833e59821546fa690bbbe3f406e97f9dcbb94524f7d4863be30eb6b14e5e9', NULL, NULL),
(45, '1231231231', 'Gustavo', 'Cuevas', '3132131312', 'gustavoalexiscuevas@gmail.com', '$2y$10$F7671oqeLkeLQEwS.uc/y.Zh2vZikwpEqewW29ikm.BZHGkWfmF/i', 'Activo', '2025-11-29 08:22:52', 1, NULL, NULL, NULL),
(48, '12231231', 'jaiderstivenson', 'Pineda', '3145970986', 'jaiderpineda203@gmail.com', '$2y$10$eI7NTRIWTO3SbrdX/b0YQ.f9xlJgIhDCBDl.o7trvd8EYNld7CwhG', 'Activo', '2025-12-05 05:43:09', 1, NULL, NULL, NULL),
(49, NULL, 'jaiderstivenson Pineda', '', '', 'jaiderpineda2003@gmail.com', '$2y$10$YJiVg0dJWRKHUfXp3MyJ1uehPjn.0EN4YnRdCSNGmSGK5LaKtrql2', 'Activo', '2025-12-05 07:44:14', 1, NULL, NULL, NULL),
(51, '1025525233', 'oihuoi', '', '7568768678', 'juliana03puentes@gmail.com', '$2y$10$TCowmmqVrAZvyVyHoKmW5ufSeGi0XDS/LLc2zFIxCvEiPL0SsLQSe', 'Activo', '2025-12-05 08:56:09', 0, '04abaef2f904b5a3a3f5576723092673e748a9f82b22a31cb8de953274fc3755', NULL, NULL),
(52, '7962931231', 'Nestor guillermo', 'Montaño', '4234234234', 'ngmonta@gmail.com', '$2y$10$/jymox/p8HxGZgXFNFM3xuPHqRXfi8P62S2xVFU1445.yWXLsPDuW', 'Activo', '2025-12-05 15:29:30', 1, NULL, NULL, NULL),
(53, '1134292423', 'Jaider', 'Pineda', '3132523414', 'jaiderpineda2004@gmail.com', '$2y$10$xTUglVIAUsFo0l6Nl8W7ne.RAemPp3mxEId2v6BmeJJh1NLZT9ynW', 'Activo', '2025-12-05 15:32:57', 0, '14cf84d39616a768b1c3e946f7f2726ac60441ff2db48f996bceeacb8603b19a', NULL, NULL),
(54, NULL, 'Jaider Pineda avila', '', '', 'pinedaavilajaider@gmail.com', '$2y$10$waeqEYqP33m2.SNMnh7JXe2SDTJnIztHtG7A32udMs5WZE/h3fcWy', 'Activo', '2025-12-06 07:10:03', 1, NULL, NULL, NULL),
(55, NULL, 'cristian camilo ulloa real', '', '', 'cristiancamiloulloa06@gmail.com', '$2y$10$xUA4NDsZSkk0Rua6vkaAIegoRDraTEAwMpzKcsw44PhPX7nGdmYk.', 'Activo', '2025-12-11 16:05:34', 1, NULL, NULL, NULL);

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
-- Estructura de tabla para la tabla `devoluciones`
--

CREATE TABLE `devoluciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `numero_orden` varchar(50) NOT NULL,
  `motivo_devolucion` text NOT NULL,
  `origen` enum('cliente','interno') NOT NULL DEFAULT 'cliente',
  `fecha_compra` date NOT NULL,
  `fecha_devolucion` date NOT NULL,
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `devoluciones`
--

INSERT INTO `devoluciones` (`id`, `cliente_id`, `proveedor_id`, `producto_id`, `cantidad`, `numero_orden`, `motivo_devolucion`, `origen`, `fecha_compra`, `fecha_devolucion`, `estado`, `observaciones`) VALUES
(1, 44, NULL, 6, 1, 'ni idea', 'ni idea', 'cliente', '2025-12-05', '2025-12-13', 'aprobada', 'no viene bien'),
(6, NULL, 5, 13, 1, '78994', 'Tapa abierta', 'interno', '2025-12-04', '2025-12-04', 'pendiente', NULL),
(7, NULL, 5, 14, 12, '75785', 'Fecha vencida', 'interno', '2025-12-06', '2025-12-07', 'aprobada', NULL);

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
(17, 3, NULL, 12, 0, 0, 0, NULL, 'disponible'),
(18, 4, 'INV-12-3951', 15, 0, 0, 0, '', 'disponible'),
(19, 5, NULL, 10, 23, 0, 0, NULL, 'disponible'),
(20, 6, '555789', 1, 3, 80, 10, 'Mueble 8', 'disponible'),
(34, 13, 'INV-16-4351', 15, 4, 20, 5, 'Vitrina 8', 'pendiente'),
(36, 14, 'INV-2-2057', 0, 0, 0, 0, '', 'agotado'),
(39, 16, 'INV-16-9478', 7, 10, 20, 3, 'Vitrina 1', 'disponible'),
(41, 25, 'INV-3-1903', 4, 1, 12, 3, 'Vitrina 7', 'agotado');

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
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock_actual` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `categoria`, `marca`, `presentacion`, `descripcion`, `stock_minimo`, `lote`, `f_vencimiento`, `precio_compra`, `precio_venta`, `iva`, `codigo_sku`, `ubicacion`, `estado`, `imagen`, `creado`, `stock_actual`) VALUES
(2, 'Proteína', 'Proteínas', 'Whey', 'En tarro', 'Para ganar masa muscular', 100, 'N/A', '2025-12-11', '80.00', '100000.00', '0.00', '78789878', 'Vitrina 4', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_69330b07e8a319.39923359.png', '2025-08-27 23:37:31', 49),
(3, 'Menta', 'Hierbas', 'Casera', 'Ramas x manojo', 'Frescura', 100, 'N/A', '2025-12-11', '1000.00', '1200.00', '0.00', NULL, 'Vitrina 1', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_69330a004d3f24.37455647.png', '2025-08-28 14:14:42', 48),
(4, 'Toronjil', 'Hierbas', 'Casera', 'Ramas x manojo', 'Hierba aromática para nervios', 100, 'N/A', '2025-12-11', '1000.00', '2000.00', '0.00', NULL, 'Vitrina 1', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_6933085d325098.97524870.png', '2025-08-28 14:15:14', 49),
(5, 'Manzanilla', 'Hierbas', 'Casera', 'Ramas x manojo', 'Aromática', 99, 'N/A', '2025-12-11', '1000.00', '2000.00', '0.00', NULL, 'Vitrina 1', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_693308e292ece7.64431140.png', '2025-08-28 14:15:56', 48),
(6, 'Diente de leon', 'Hierbas', 'Casera', 'Ramas x manojo', 'Trastornos digestivos leves', 100, 'N/A', '2025-12-11', '500.00', '1500.00', '0.00', NULL, 'Vitrina 1', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_6933094692c640.26469423.png', '2025-08-28 14:15:56', 47),
(13, 'Vitamina C', 'Vitaminas', 'Healthy America Colombia SAS', 'Capsula 1000MG', 'ES UN SUPLEMENTO DIETARIO', 2, 'SD2015-0003704', '2025-12-10', '25000.00', '50000.00', '0.00', '565465', 'Mueble 8', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_693309c5a2f846.50062225.png', '2025-12-05 01:01:23', 50),
(14, 'Oxido de magnesio', 'Minerales', 'Healthy America Colombia SAS', 'Capsula 100 MG', 'Para los huesos', 3, '121323', '2025-12-26', '20000.00', '60000.00', '0.00', '5667676', 'Vitrina 6', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_6933099626aa07.24054122.png', '2025-12-05 01:30:20', 50),
(16, 'Canela', 'Hierbas', 'Casera', 'Polvo X 8 G', 'Cólicos', 5, 'N/A', '2025-12-28', '700.00', '2100.00', '0.00', NULL, 'Mueble3', 'inactivo', 'https://hieribal.byethost3.com/public/assets/img/prod_6932f189aca661.39174005.png', '2025-12-05 14:51:53', 50),
(17, 'Proteína de fresa', 'Proteínas', 'Whey', 'En tarro', 'Ganancia Muscular', 50, 'N/A', '2026-04-30', '200.00', '250000.00', '0.01', '4678445', 'Vitrina 4', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_69330e398ccb39.81266524.png', '2025-12-05 16:54:17', 0),
(18, 'Proteína premium', 'Proteínas', 'Whey', 'En tarro', 'Proteína es para crecimiento muscular', 50, 'N/A', '2026-11-28', '100000.00', '180000.00', '0.00', NULL, '', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_69331636ce5d36.08056720.png', '2025-12-05 17:28:22', 0),
(19, 'Combo de Proteínas', 'Proteínas', 'Variada', 'En tarro', 'Ganancia muscular y para tonificación', 70, '234532', '2027-01-27', '150000.00', '270000.00', '0.01', '445789644', 'Vitrina 4', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_693317934703e3.29337760.png', '2025-12-05 17:34:11', 0),
(20, 'Proteína Total', 'Proteínas', 'Total', 'En tarro', 'Crecimiento Muscular', 30, '31', '2027-02-10', '600000.00', '800000.00', '0.00', NULL, '', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_693318d8664984.87811284.png', '2025-12-05 17:39:36', 0),
(23, 'Aceite de té de arbol', 'Aceites', 'Eva natur', 'Fascro de vidrio', 'Es para el uso de masajes', 20, 'N/A', '2027-01-14', '10000.00', '16000.00', '0.00', '4567743245', 'Vitrina 2', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_693d9e5603f763.08128365.jpg', '2025-12-13 17:11:49', 0),
(24, 'Aceite de oliva', 'Aceites', 'Olitalia', 'En tarro', 'Aceite extra virgen naturista', 10, 'N/A', '2027-02-24', '11000.00', '18000.00', '0.00', '56575454364', 'Vitrina 2', 'activo', 'https://hieribal.byethost3.com/public/assets/img/prod_693da022350321.63513223.jpg', '2025-12-13 17:18:29', 0),
(25, 'Miel de propóleo', 'Mieles', 'Sierra', 'En frasco', 'Esta miel es orgánica en su totalidad', 5, 'N/A', '2026-07-22', '20000.00', '30000.00', '0.00', '578838394', 'Vitrina 3', 'inactivo', 'https://hieribal.byethost3.com/public/assets/img/prod_693da1312fed03.75398652.jpg', '2025-12-13 17:24:01', 0);

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
(5, 'Healthy America Colombia SAS', '9990151546', 'Roberto Perez', '3134405383', 'robertoperez@healthyamerica.com', 'Centro de Bogotá', 'Bogota DC', 'Daviplata  Nequi  Efectivo', 'activo', '2025-12-05 01:16:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor_producto`
--

CREATE TABLE `proveedor_producto` (
  `id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedor_producto`
--

INSERT INTO `proveedor_producto` (`id`, `proveedor_id`, `producto_id`, `precio_compra`, `activo`, `creado`) VALUES
(3, 3, 2, '2000.00', 1, '2025-12-04 21:16:52'),
(5, 4, 5, '100000.00', 1, '2025-12-04 22:55:15'),
(6, 4, 3, '5000.00', 1, '2025-12-04 22:55:15'),
(9, 5, 13, '50000.00', 1, '2025-12-05 23:12:56'),
(10, 5, 14, '80000.00', 1, '2025-12-05 23:12:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte_venta`
--

CREATE TABLE `reporte_venta` (
  `id` int(11) UNSIGNED NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
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
(1, '5009', 2, 1, '2000.00', '2000.00', NULL, 48, 'efectivo', '2025-12-05', NULL, '2025-12-05 16:53:34'),
(3, '5005', 2, 1, '2000.00', '2000.00', NULL, 48, 'efectivo', '2025-12-04', NULL, '2025-12-04 15:46:08'),
(4, '5006', 3, 3, '1200.00', '3600.00', NULL, 48, 'efectivo', '2025-12-04', NULL, '2025-12-04 15:49:12'),
(5, '5007', 5, 1, '1500.00', '1500.00', NULL, 48, 'efectivo', '2025-12-04', NULL, '2025-12-04 16:02:58'),
(6, '5008', 2, 3, '2000.00', '6000.00', NULL, 48, 'efectivo', '2025-12-04', NULL, '2025-12-04 16:30:28'),
(10, '5016', 3, 1, '1200.00', '1200.00', NULL, 48, 'efectivo', '2025-12-05', NULL, '2025-12-05 17:05:36'),
(11, '5017', 5, 1, '2000.00', '2000.00', NULL, 48, 'efectivo', '2025-12-05', NULL, '2025-12-05 17:06:42'),
(12, '5018', 4, 1, '2000.00', '2000.00', NULL, 48, 'efectivo', '2025-12-05', NULL, '2025-12-05 23:42:44'),
(13, '5018', 6, 1, '1500.00', '1500.00', NULL, 48, 'efectivo', '2025-12-05', NULL, '2025-12-05 23:42:44'),
(14, '1415', 3, 2, '10000.00', '20000.00', 45, 41, 'efectivo', '2025-12-02', 'nada.', '2025-12-11 01:08:50');

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
(41, 'Cajero', '$2y$10$4jxWXVLCXSGTMDyot5zimeBGsF7/WdlkHkUjLAaedzMGKgNbWDIHi', 'Cajero', 'Juan Pepito', 'Martinez Hernandezz', 'gustavoalexis@gmail.com', 0, '9fc34c171c710ac435f2928dc91dc195', '2025-11-20 19:15:22', '2024-12-04 20:40:29', 'Activo', NULL),
(48, 'Admin2', '$2y$10$Lu2Vp7ehIGMrgCoFGYRQReRj7Yg3V9PFArO5lNlzKdIACJr2IrzFK', 'Admin', 'Gustavo', 'Cuevas', 'gustavoalexiscuevas@gmail.com', 1, NULL, NULL, '2025-11-19 19:44:44', 'Activo', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) UNSIGNED NOT NULL,
  `id_carrito` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `pago_con` decimal(10,2) DEFAULT NULL,
  `cambio` decimal(10,2) DEFAULT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `metodo_pago` varchar(50) DEFAULT 'Efectivo',
  `nombre_cliente` varchar(100) DEFAULT NULL,
  `apellido_cliente` varchar(100) DEFAULT NULL,
  `cedula_cliente` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `id_carrito`, `total`, `pago_con`, `cambio`, `fecha_venta`, `metodo_pago`, `nombre_cliente`, `apellido_cliente`, `cedula_cliente`) VALUES
(5005, NULL, '2000.00', '50000.00', '48000.00', '2025-12-04 15:46:08', 'efectivo', 'gustavo', 'cuevas', '1000789324'),
(5006, NULL, '3600.00', '50000.00', '46400.00', '2025-12-04 15:49:12', 'efectivo', 'gustavo', 'cuevas', '1000789324'),
(5007, NULL, '1500.00', '60000.00', '58500.00', '2025-12-04 16:02:58', 'efectivo', 'gustavo', 'cuevas', '1000789324'),
(5008, NULL, '6000.00', '50000.00', '44000.00', '2025-12-04 16:30:28', 'efectivo', 'gustavo', 'cuevas', '1000789324'),
(5009, NULL, '2000.00', '5000.00', '3000.00', '2025-12-05 16:53:34', 'efectivo', 'gustavo', 'cuevas', '1000789324'),
(5013, NULL, '2000.00', '2000.00', '0.00', '2025-12-05 16:59:47', 'efectivo', 'Omar', 'Camargo', '1134678626'),
(5014, NULL, '1500.00', '3000.00', '1500.00', '2025-12-05 17:00:35', 'efectivo', 'Andres', 'González', '1134225746'),
(5015, NULL, '1200.00', '4000.00', '2800.00', '2025-12-05 17:02:46', 'efectivo', 'cristian', 'cuevas', '1000789322'),
(5016, NULL, '1200.00', '1200.00', '0.00', '2025-12-05 17:05:36', 'efectivo', 'Maria', 'Ecobar', '2354267777'),
(5017, NULL, '2000.00', '4000.00', '2000.00', '2025-12-05 17:06:42', 'efectivo', 'Maria', 'Ecobar', '2354267777'),
(5018, NULL, '3500.00', '50000.00', '46500.00', '2025-12-05 23:42:44', 'efectivo', 'Pepito', 'Perez', '1111111111');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `fk_carrito_productos` (`id_producto`),
  ADD KEY `fk_carrito_clientes` (`id_cliente`);

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
-- Indices de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_devol_producto` (`producto_id`),
  ADD KEY `fk_devoluciones_proveedor` (`proveedor_id`),
  ADD KEY `fk_devol_cliente` (`cliente_id`);

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
-- Indices de la tabla `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pp_proveedor` (`proveedor_id`),
  ADD KEY `idx_pp_producto` (`producto_id`);

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
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3029;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `historial_pedido`
--
ALTER TABLE `historial_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `reporte_venta`
--
ALTER TABLE `reporte_venta`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5019;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
