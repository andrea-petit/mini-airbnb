-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-01-2026 a las 20:36:31
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
-- Base de datos: `mini_airbnb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comodidades`
--

CREATE TABLE `comodidades` (
  `id_comodidad` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comodidades`
--

INSERT INTO `comodidades` (`id_comodidad`, `nombre`) VALUES
(1, 'Wifi'),
(2, 'Cocina'),
(3, 'Aire acondicionado'),
(4, 'Estacionamiento'),
(5, 'TV'),
(6, 'Lavadora');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades`
--

CREATE TABLE `propiedades` (
  `id_propiedad` int(11) NOT NULL,
  `id_anfitrion` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_noche` decimal(10,2) NOT NULL,
  `ubicacion` varchar(200) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `capacidad` int(11) DEFAULT 1,
  `disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `propiedades`
--

INSERT INTO `propiedades` (`id_propiedad`, `id_anfitrion`, `titulo`, `descripcion`, `precio_noche`, `ubicacion`, `imagen_url`, `capacidad`, `disponible`) VALUES
(10, 8, 'Mi casa', 'mi casa a la venta. SI', 100.00, 'La Puerta Maraven', '1769797483_697cf76b4b9f3.jpeg', 10, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedad_comodidades`
--

CREATE TABLE `propiedad_comodidades` (
  `id_propiedad` int(11) NOT NULL,
  `id_comodidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `propiedad_comodidades`
--

INSERT INTO `propiedad_comodidades` (`id_propiedad`, `id_comodidad`) VALUES
(10, 1),
(10, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `id_propiedad` int(11) NOT NULL,
  `id_huesped` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `cant_huespedes` int(11) NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `id_propiedad`, `id_huesped`, `fecha_inicio`, `fecha_fin`, `precio_total`, `cant_huespedes`, `estado`, `creado_en`) VALUES
(7, 10, 5, '2026-01-30', '2026-01-31', 100.00, 1, 'cancelada', '2026-01-30 19:11:12'),
(8, 10, 5, '2026-01-30', '2026-01-31', 1000.00, 10, 'cancelada', '2026-01-30 19:34:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nro_tlf` varchar(11) NOT NULL,
  `rol` enum('anfitrion','huesped') DEFAULT 'huesped',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `username`, `email`, `password`, `nro_tlf`, `rol`, `creado_en`) VALUES
(5, 'andrea', 'andrea@prueba.com', '$2y$10$l0bjFnB2V11J/5.KO1Doa.N60WOlNJAt0/Ort8dov0AYQ3Fbp0Ea2', '04246304379', 'huesped', '2026-01-30 18:04:00'),
(6, 'yope', 'yope@gmail.com', '$2y$10$XrctgDDF8SXJlfPj..4Yq.FHvrtPMRaENxyb0anv41VEgCn41QMgm', '4146574687', 'huesped', '2026-01-30 18:14:39'),
(8, 'Andres', 'andres@prueba.com', '$2y$10$wo7uo2n40pO8wpCvRKfApOjMk3PKIjLehKYQbSZ576S1RgqnGTx7i', '4141234567', 'anfitrion', '2026-01-30 18:22:16');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comodidades`
--
ALTER TABLE `comodidades`
  ADD PRIMARY KEY (`id_comodidad`);

--
-- Indices de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD PRIMARY KEY (`id_propiedad`),
  ADD KEY `id_anfitrion` (`id_anfitrion`);

--
-- Indices de la tabla `propiedad_comodidades`
--
ALTER TABLE `propiedad_comodidades`
  ADD PRIMARY KEY (`id_propiedad`,`id_comodidad`),
  ADD KEY `fk_comodidad` (`id_comodidad`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `id_propiedad` (`id_propiedad`),
  ADD KEY `id_huesped` (`id_huesped`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nro_tlf` (`nro_tlf`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comodidades`
--
ALTER TABLE `comodidades`
  MODIFY `id_comodidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  MODIFY `id_propiedad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD CONSTRAINT `propiedades_ibfk_1` FOREIGN KEY (`id_anfitrion`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `propiedad_comodidades`
--
ALTER TABLE `propiedad_comodidades`
  ADD CONSTRAINT `fk_comodidad` FOREIGN KEY (`id_comodidad`) REFERENCES `comodidades` (`id_comodidad`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_propiedad` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id_propiedad`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id_propiedad`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_huesped`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
