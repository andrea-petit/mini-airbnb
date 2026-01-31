-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-01-2026 a las 00:37:51
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
-- Estructura de tabla para la tabla `preguntas_seguridad`
--

CREATE TABLE `preguntas_seguridad` (
  `id_pregunta` int(11) NOT NULL,
  `pregunta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_seguridad`
--

INSERT INTO `preguntas_seguridad` (`id_pregunta`, `pregunta`) VALUES
(1, '¿Cuál es el nombre de tu primera mascota?'),
(2, '¿En qué ciudad naciste?'),
(3, '¿Cuál es el nombre de tu escuela primaria?'),
(4, '¿Cuál es la marca de tu primer coche?'),
(5, '¿Cómo se llamaba tu mejor amigo de la infancia?');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades`
--

CREATE TABLE `propiedades` (
  `id_propiedad` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `id_anfitrion` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_noche` decimal(10,2) NOT NULL,
  `ubicacion` varchar(200) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `capacidad` int(11) DEFAULT 1,
  `disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedad_comodidades`
--

CREATE TABLE `propiedad_comodidades` (
  `id_propiedad` int(11) NOT NULL,
  `id_comodidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(12, 'Andres', 'andres@gmail.com', '$2y$10$ZO1x8iiRBxCPqNBZayVtEu62P5GwUXC6D9TJ0Ss2TL0b5J9JW23om', '4145674687', 'anfitrion', '2026-01-30 21:22:37'),
(13, 'Veronika', 'vero@gmail', '$2y$10$TRo6AdDhQ0/Wc6tXsA/aG.6EwYbjk.1ytIOxWSkemR1ibg1NC6OEu', '1234567887', 'huesped', '2026-01-30 22:37:33'),
(14, 'Andrea', 'andrea@gmail.com', '$2y$10$97YeanlchR2miHljscvfqu8LZ88HfjHVPf3eCVeSwH4TVTNfXdHqK', '4146574687', 'huesped', '2026-01-30 22:45:54'),
(15, '&lt;script&gt;alert(&#039;Tu web es vulnerable&#039;);&lt;/script&gt;', '123@gmail', '$2y$10$FsO3Q4CmZqNousPC15DqIe60QEHreOH3omlByTBbSL5R53vUyG4g2', '1111111111', 'huesped', '2026-01-30 23:03:42'),
(16, 'Veronika', 'verito@gmail', '$2y$10$E5cCTKhlhIr0OpW/PZEZUuyClkOyCVss03Yt7m0VNZOdlw2VqhjdC', '3333333333', 'anfitrion', '2026-01-30 23:08:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_preguntas`
--

CREATE TABLE `usuarios_preguntas` (
  `id_usuario_pregunta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `respuesta_hash` varchar(255) NOT NULL,
  `fecha_configuracion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_preguntas`
--

INSERT INTO `usuarios_preguntas` (`id_usuario_pregunta`, `id_usuario`, `id_pregunta`, `respuesta_hash`, `fecha_configuracion`) VALUES
(3, 12, 4, '$2y$10$Z1xX95M00HP46jw0/dDOzuo5PgRazyBCo457i5tlgpJe7zBOhHjJG', '2026-01-30 21:23:07'),
(4, 13, 2, '$2y$10$aq72r3gBemmqt5p7EgBT6emB6HTvG83BAEBPyl38VT7RKoXB22iDq', '2026-01-30 22:37:38'),
(5, 14, 4, '$2y$10$TdZHK9Ux/rI4PUcYDLrvMuzMrnGOISNoLnQcbFmG1vKMiZFzQ6YX.', '2026-01-30 22:46:04'),
(6, 16, 1, '$2y$10$gC2nnuBsmPp17EYqBEvF1ukoLex7AS02.7XnoAZhe9DctJUjbbSYa', '2026-01-30 23:08:16');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comodidades`
--
ALTER TABLE `comodidades`
  ADD PRIMARY KEY (`id_comodidad`);

--
-- Indices de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD PRIMARY KEY (`id_pregunta`);

--
-- Indices de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD PRIMARY KEY (`id_propiedad`),
  ADD UNIQUE KEY `uuid` (`uuid`),
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
-- Indices de la tabla `usuarios_preguntas`
--
ALTER TABLE `usuarios_preguntas`
  ADD PRIMARY KEY (`id_usuario_pregunta`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_pregunta` (`id_pregunta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comodidades`
--
ALTER TABLE `comodidades`
  MODIFY `id_comodidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  MODIFY `id_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  MODIFY `id_propiedad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuarios_preguntas`
--
ALTER TABLE `usuarios_preguntas`
  MODIFY `id_usuario_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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

--
-- Filtros para la tabla `usuarios_preguntas`
--
ALTER TABLE `usuarios_preguntas`
  ADD CONSTRAINT `usuarios_preguntas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_preguntas_ibfk_2` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas_seguridad` (`id_pregunta`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
