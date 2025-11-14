-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-11-2025 a las 01:53:52
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
-- Base de datos: `mi_hogar_en_linea`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_propiedad`
--

CREATE TABLE `imagenes_propiedad` (
  `id` int(11) NOT NULL,
  `id_propiedad` int(11) NOT NULL,
  `imagen_url` varchar(255) NOT NULL,
  `es_principal` tinyint(4) DEFAULT 0,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_propiedad`
--

INSERT INTO `imagenes_propiedad` (`id`, `id_propiedad`, `imagen_url`, `es_principal`, `fecha_subida`) VALUES
(1, 1, 'uploads/properties/1/6900ba9c8e7e0.png', 1, '2025-10-28 12:44:12'),
(2, 2, 'uploads/properties/2/6915fcc54c1b0.jpg', 1, '2025-11-13 15:44:05'),
(4, 4, 'uploads/properties/4/691620fb9b191.png', 1, '2025-11-13 18:18:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `id_propiedad` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades`
--

CREATE TABLE `propiedades` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('apartamento','casa','estudio','duplex','penthouse') NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `ubicacion` varchar(200) NOT NULL,
  `direccion` text DEFAULT NULL,
  `habitaciones` int(11) DEFAULT NULL,
  `banos` int(11) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `amueblado` tinyint(4) DEFAULT 0,
  `mascotas` tinyint(4) DEFAULT 0,
  `estacionamiento` tinyint(4) DEFAULT 0,
  `estado` enum('disponible','arrendada','inactiva') DEFAULT 'disponible',
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `propiedades`
--

INSERT INTO `propiedades` (`id`, `id_usuario`, `titulo`, `descripcion`, `tipo`, `precio`, `ubicacion`, `direccion`, `habitaciones`, `banos`, `area`, `amueblado`, `mascotas`, `estacionamiento`, `estado`, `fecha_publicacion`, `fecha_actualizacion`) VALUES
(1, 2, 'Omnis itaque obcaeca', 'Expedita animi sed ', 'duplex', 612.00, 'Reiciendis eum omnis', 'Dolor velit eaque re', 3, 1, 95.00, 0, 1, 1, 'disponible', '2025-10-28 12:44:12', '2025-10-28 12:44:12'),
(2, 5, 'Apartamento', 'sdhi vdfnvnf s fn fdn i njcsk nfad  kjzfk jd ncxnjnj', 'apartamento', 450.00, 'B/ Porvenir', 'Calle 34 # 18 - 34', 2, 1, 220.00, 0, 1, 1, 'disponible', '2025-11-13 15:44:05', '2025-11-13 15:44:05'),
(4, 6, 'Uniclaretiana', 'La mejor universidad del mundo', 'estudio', 200.00, 'B/ Yesquita', 'Calle 34 # 18 - 34', 5, 4, 200.00, 1, 0, 1, 'disponible', '2025-11-13 18:18:35', '2025-11-13 18:18:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `whatsapp`, `avatar`, `fecha_registro`, `activo`) VALUES
(1, 'Juan Martínez', 'juan@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, '2025-10-28 11:51:10', 1),
(2, 'Cupiditate ipsam vel', 'sekybyx@mailinator.com', '$2y$10$IA.rdmzWj4B8NWificC2DeppKesOfiiOJZIgMgKC4ahSd/grLm6bm', '+1 (636) 193-8205', NULL, NULL, '2025-10-28 12:41:11', 1),
(3, 'Brayan Valencia', 'brayansmith1818@gmail.com', '$2y$10$JU/5N03b0JHNNnpt3y3OROV5202vkh5R1M17B/73ue70PtfGfG2.W', '3148729333', NULL, NULL, '2025-11-05 03:05:30', 1),
(4, 'Luvis Yorfari', 'yorfariluvis@gmail.com', '$2y$10$83bejLRnnZITuC0qZUtJ3.FD6Nz4K7WtrLSjLEpbsSQ57Nvxd4Sza', '3216720152', NULL, NULL, '2025-11-06 12:33:55', 1),
(5, 'Brayan Valencia', 'brayansmith1829@gmail.com', '$2y$10$cX/2OtrKcGGb0giwNN0T1ehz6BXa6yVdGy5kwMQV4.MidKRbseQ1y', '3113263221', '3113263221', NULL, '2025-11-13 15:41:58', 1),
(6, 'Luvis Yorfari', 'yorfariluvis1@gmail.com', '$2y$10$A/SdgIWSyLmNR4K3fnbF0.vX.k1QZUzgDLipXA/W11wKF4p95BHWi', '3216720152', '3216720152', NULL, '2025-11-13 17:10:16', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `imagenes_propiedad`
--
ALTER TABLE `imagenes_propiedad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_propiedad` (`id_propiedad`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_propiedad` (`id_propiedad`);

--
-- Indices de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `imagenes_propiedad`
--
ALTER TABLE `imagenes_propiedad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `imagenes_propiedad`
--
ALTER TABLE `imagenes_propiedad`
  ADD CONSTRAINT `imagenes_propiedad_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD CONSTRAINT `propiedades_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
