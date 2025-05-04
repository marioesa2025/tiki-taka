-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-05-2025 a las 22:55:44
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
-- Base de datos: `apuestas_online`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_actions`
--

CREATE TABLE `admin_actions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apuestas`
--

CREATE TABLE `apuestas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `sorteo_id` int(11) NOT NULL,
  `estrellas` int(11) NOT NULL CHECK (`estrellas` between 1 and 5),
  `monto` decimal(10,2) GENERATED ALWAYS AS (`estrellas` * 1000) STORED,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `editable` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `detalles` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes_sorteos`
--

CREATE TABLE `participantes_sorteos` (
  `id` int(11) NOT NULL,
  `sorteo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_participacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recargas`
--

CREATE TABLE `recargas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `estado` enum('pendiente','completada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sorteos`
--

CREATE TABLE `sorteos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `premio` varchar(255) NOT NULL,
  `max_participantes` int(11) NOT NULL,
  `estado` enum('pendiente','activo','completado','cancelado') DEFAULT 'pendiente',
  `ganador_id` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sorteos`
--

INSERT INTO `sorteos` (`id`, `nombre`, `descripcion`, `fecha_inicio`, `fecha_fin`, `premio`, `max_participantes`, `estado`, `ganador_id`, `fecha_creacion`) VALUES
(1, '1ro', NULL, '2025-05-03 01:21:00', '2025-05-04 01:21:00', '75%', 100, 'pendiente', NULL, '2025-05-02 04:22:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT 'default.jpg',
  `saldo` decimal(10,2) DEFAULT 0.00,
  `aprobado` tinyint(1) DEFAULT 0,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `es_admin` tinyint(1) DEFAULT 0,
  `ultimo_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `foto_perfil`, `saldo`, `aprobado`, `fecha_registro`, `es_admin`, `ultimo_login`) VALUES
(7, 'admin', 'admin@gmail.com', '$2y$10$LDtiAx3P3.lJLAnqYOoU9uLmDqXthwTc64HF4XtzgdkjKTXjAtkXi', 'admin.jpg', 5000.00, 1, '2025-04-27 17:29:23', 1, '2025-04-30 20:29:13'),
(9, 'mago', 'mago@gmail.com', '$2y$10$fLd5y.Jrmj1VPwPzA0Wr0.HFy1trWjMz7dxTTnssDjt88hUyA0wNG', 'mago.jpg', 0.00, 1, '2025-04-27 21:50:37', 0, NULL),
(10, 'marcelo', 'marioesa@hotmail.com', '$2y$10$UvmesKqNMIthsAtFzh2Ji.KmbE6qA0o3N7RPS5dz1.Ly3zsPGxUGi', 'default.jpg', 0.00, 0, '2025-04-28 00:51:36', 0, NULL),
(11, 'roberto nuñez', 'roberto@gmail.com', '$2y$10$kcKcHeXNwxUiCrocIdcjCOWzJBn9fOff7eImyZ4dr9HutPQwaQhDK', 'default.jpg', 0.00, 0, '2025-04-28 10:36:35', 0, NULL),
(12, 'roberto sosa', 'rosa@gmail.com', '$2y$10$21DUBq0T9rN8OUtRC1n0/u4l28/YF.j9CtzqfsL.EHvq/WqZsOsd6', 'perfil_680f8690013ed.png', 0.00, 0, '2025-04-28 10:45:52', 0, NULL),
(13, 'roberto lias', 'rosa2@gmail.com', '$2y$10$73eO50wtRkqZ3.eB88ENTu5ixmuz2qRmWe6kDRkniIoO0CAOT83zK', 'perfil_680f89601a008.jpg', 5000.00, 0, '2025-04-28 10:57:52', 0, NULL),
(14, 'ale', 'ale@gmail.com', '$2y$10$WW/0Kgyi7r1yDAFN5lcdJeVx3M0YMsFAfWPmBzrLHLN6egd846AAe', 'perfil_6812a88fb4708.jpg', 5000.00, 1, '2025-04-30 19:47:43', 0, '2025-04-30 20:31:11');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `apuestas`
--
ALTER TABLE `apuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `sorteo_id` (`sorteo_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `participantes_sorteos`
--
ALTER TABLE `participantes_sorteos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sorteo_id` (`sorteo_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `recargas`
--
ALTER TABLE `recargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `sorteos`
--
ALTER TABLE `sorteos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ganador_id` (`ganador_id`);

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
-- AUTO_INCREMENT de la tabla `admin_actions`
--
ALTER TABLE `admin_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `apuestas`
--
ALTER TABLE `apuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `participantes_sorteos`
--
ALTER TABLE `participantes_sorteos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recargas`
--
ALTER TABLE `recargas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sorteos`
--
ALTER TABLE `sorteos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD CONSTRAINT `admin_actions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `admin_actions_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `apuestas`
--
ALTER TABLE `apuestas`
  ADD CONSTRAINT `apuestas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `apuestas_ibfk_2` FOREIGN KEY (`sorteo_id`) REFERENCES `sorteos` (`id`);

--
-- Filtros para la tabla `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `participantes_sorteos`
--
ALTER TABLE `participantes_sorteos`
  ADD CONSTRAINT `participantes_sorteos_ibfk_1` FOREIGN KEY (`sorteo_id`) REFERENCES `sorteos` (`id`),
  ADD CONSTRAINT `participantes_sorteos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `recargas`
--
ALTER TABLE `recargas`
  ADD CONSTRAINT `recargas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `sorteos`
--
ALTER TABLE `sorteos`
  ADD CONSTRAINT `sorteos_ibfk_1` FOREIGN KEY (`ganador_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
