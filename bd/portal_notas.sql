-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-08-2026 a las 15:59:16
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
-- Base de datos: `portal_notas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `especialidad` varchar(150) DEFAULT NULL,
  `rol` enum('admin','profesor') NOT NULL DEFAULT 'admin',
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `nombre`, `usuario`, `password`, `correo`, `telefono`, `especialidad`, `rol`, `creado_en`) VALUES
(1, 'Secretaría Académica', 'admin', '$2y$10$I2tCazIvrLhLAIZ3gK0F/.YhfoSQ9clCS25v8KxTjagonp9JGb8MS', 'secretaria@instituto.edu.sv', NULL, NULL, 'admin', '2026-07-23 08:55:46'),
(2, 'Juan Romero', 'jromero', '$2y$10$MC/MecH4x0EL66lVfZwmfecMvJkZlNxY3ugX9lLLpAXkg9tEmYBVO', 'juanromero1@gmail.com', '3728-8292', 'Educacion Fisica', 'profesor', '2026-07-28 20:27:29'),
(3, 'Lic. Pedro Ramírez', 'pramirez', '$2b$10$6lXNxUgjq/8JJeYxM4F6Z.pOUl4HInfCncWo6d9X8usbAsZD6s3g.', 'pedro.ramirez@instituto.edu.sv', '7000-0001', 'Matemática', 'profesor', '2026-07-28 20:51:48'),
(4, 'Licda. Carmen Ruiz', 'cruiz', '$2b$10$4p9Pd.kaoW7GlcQ8.Wryjeq1T.L9KKPlZu0i.UiuOtvGgCE1/UJPy', 'carmen.ruiz@instituto.edu.sv', '7000-0002', 'Lenguaje y Literatura', 'profesor', '2026-07-28 20:51:48'),
(5, 'Ing. Luis Hernández', 'lhernandez', '$2b$10$D/ngEAa2HgYBF/mvFn6k7erK5KXlMCe7G5yIK061jJGYkR46uYFS6', 'luis.hernandez@instituto.edu.sv', '7000-0003', 'Programación Web', 'profesor', '2026-07-28 20:51:48'),
(6, 'Licda. Ana Beltrán', 'abeltran', '$2b$10$PBJS4YN9jhqRr.EHUk.j4OhbjqDi4CUjccIgYjzVjHPJVxdmlCnSi', 'ana.beltran@instituto.edu.sv', '7000-0004', 'Inglés', 'profesor', '2026-07-28 20:51:48'),
(7, 'Ing. Marta Solís', 'msolis', '$2b$10$BAvg1ja7vpT/kzcrS2Loru2pS.Hk8S6gAYnBtmpDqPxMmG6iGoae2', 'marta.solis@instituto.edu.sv', '7000-0005', 'Física', 'profesor', '2026-07-28 20:51:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('Presente','Ausente','Tardanza') NOT NULL DEFAULT 'Presente',
  `observacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id`, `id_estudiante`, `fecha`, `estado`, `observacion`) VALUES
(1, 1, '2026-07-13', 'Presente', NULL),
(2, 1, '2026-07-14', 'Tardanza', 'Llegó 15 min tarde'),
(3, 2, '2026-07-13', 'Ausente', 'Cita médica'),
(4, 3, '2026-07-13', 'Presente', NULL),
(5, 4, '2026-08-26', 'Tardanza', 'Tardo media hora en llegar a clases');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avisos`
--

CREATE TABLE `avisos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `avisos`
--

INSERT INTO `avisos` (`id`, `titulo`, `contenido`, `id_admin`, `fecha`) VALUES
(1, 'Entrega de boletines', 'El próximo viernes se entregarán los boletines del I Trimestre en la dirección del instituto.', 1, '2026-07-23 08:55:46'),
(2, 'Reunión de padres de familia', 'Se convoca a reunión general el sábado 25 de julio a las 9:00 a.m. en el auditorio.', 1, '2026-07-23 08:55:46'),
(3, 'Simulacro 30/07', 'Realizacion de simulacro el dia jueves a las 9:00', 1, '2026-07-28 20:40:10'),
(4, 'Acto de lunes civico para todos los estudiantes', 'Todos los estudiantes tienen que estar en el acto de lunes civico, obligatorio', 1, '2026-08-25 17:42:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conceptos_pago`
--

CREATE TABLE `conceptos_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `monto_default` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `conceptos_pago`
--

INSERT INTO `conceptos_pago` (`id`, `nombre`, `monto_default`, `activo`, `creado_en`) VALUES
(1, 'Mensualidad', 45.00, 1, '2026-08-21 13:04:11'),
(2, 'Matrícula', 60.00, 1, '2026-08-21 13:04:11'),
(3, 'Uniforme', 35.00, 1, '2026-08-21 13:04:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conducta`
--

CREATE TABLE `conducta` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text NOT NULL,
  `tipo` enum('Positiva','Negativa','Neutra') NOT NULL DEFAULT 'Neutra',
  `id_admin` int(11) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `conducta`
--

INSERT INTO `conducta` (`id`, `id_estudiante`, `fecha`, `descripcion`, `tipo`, `id_admin`, `creado_en`) VALUES
(1, 1, '2026-07-29', 'Buena alumna', 'Positiva', 3, '2026-07-28 20:54:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int(11) NOT NULL,
  `carnet` varchar(20) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `grado` varchar(40) DEFAULT NULL,
  `seccion` varchar(10) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `id_padre` int(11) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `carnet`, `nombre`, `apellido`, `grado`, `seccion`, `fecha_nacimiento`, `id_padre`, `creado_en`) VALUES
(1, '2026-001', 'Ana', 'García', '3er Año Bachillerato', 'A', '2009-03-12', 1, '2026-07-23 08:55:46'),
(2, '2026-002', 'Carlos', 'García', '1er Año Bachillerato', 'B', '2010-07-25', 1, '2026-07-23 08:55:46'),
(3, '2026-003', 'Sofía', 'López', '3er Año Bachillerato', 'A', '2008-11-05', 2, '2026-07-23 08:55:46'),
(4, '2026-004', 'Francisco', 'Torres', '2do Salud y Bienestar Social', 'B', '2009-09-12', 2, '2026-08-25 17:37:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `docente` varchar(100) DEFAULT NULL,
  `grado` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `nombre`, `docente`, `grado`) VALUES
(1, 'Matemática', 'Lic. Pedro Ramírez', '2do Año Bachillerato'),
(2, 'Lenguaje y Literatura', 'Licda. Carmen Ruiz', '2do Año Bachillerato'),
(3, 'Programación Web', 'Ing. Luis Hernández', '2do Año Bachillerato'),
(4, 'Inglés', 'Licda. Ana Beltrán', '1er Año Bachillerato'),
(5, 'Física', 'Ing. Marta Solís', '3er Año Bachillerato');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensualidades`
--

CREATE TABLE `mensualidades` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_concepto` int(11) NOT NULL,
  `periodo_mes` varchar(20) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_limite` date NOT NULL,
  `estado` enum('Pendiente','Pagado','Vencido','Anulado') NOT NULL DEFAULT 'Pendiente',
  `recordatorio_enviado_en` datetime DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_periodo` int(11) NOT NULL,
  `nota` decimal(4,2) NOT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`id`, `id_estudiante`, `id_materia`, `id_periodo`, `nota`, `comentario`, `fecha_registro`) VALUES
(1, 1, 1, 1, 8.50, 'Buen desempeño en exámenes', '2026-07-23 08:55:46'),
(2, 1, 2, 1, 9.00, NULL, '2026-07-23 08:55:46'),
(3, 1, 3, 1, 9.50, 'Excelente proyecto final', '2026-07-23 08:55:46'),
(4, 2, 4, 1, 7.80, NULL, '2026-07-23 08:55:46'),
(5, 3, 5, 1, 8.20, 'Necesita reforzar laboratorio', '2026-07-23 08:55:46'),
(6, 2, 5, 3, 9.00, NULL, '2026-08-25 20:13:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `padres`
--

CREATE TABLE `padres` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `padres`
--

INSERT INTO `padres` (`id`, `nombre`, `apellido`, `correo`, `telefono`, `usuario`, `password`, `creado_en`) VALUES
(1, 'María', 'García', 'maria.garcia@correo.com', '7000-1111', 'mgarcia', '$2y$10$x6RHvUKq0/eicCWsfzV/FeQvPFCM61lZOJGhSLbGnCgFSuUwNGu/S', '2026-07-23 08:55:46'),
(2, 'José', 'López', 'jose.lopez@correo.com', '7000-2222', 'jlopez', '$2y$10$x6RHvUKq0/eicCWsfzV/FeQvPFCM61lZOJGhSLbGnCgFSuUwNGu/S', '2026-07-23 08:55:46'),
(3, 'Hermenegildo', 'Trujillo', 'htrujillo1@gmail.com', '7000-2211', 'htrujillo', '$2y$10$OLh03QpKuwvRld7bkrC96eQNP1uZhh/NWQZLSoibHFOL1j0DoW8N.', '2026-08-25 17:39:11'),
(4, 'German', 'Alcaraz', 'agerman1@gmail.com', '8003-3322', 'ggerman', '$2y$10$m/wqa2ZHxSYpykYvc9S3KO8EANXzs2Z8JhHvb5rGJgzLimMFJlU0K', '2026-08-27 08:07:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `folio` varchar(20) NOT NULL,
  `id_padre` int(11) DEFAULT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','Tarjeta','QR','PayPal','Visa') NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pagado','Anulado') NOT NULL DEFAULT 'Pagado',
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `anulado_en` datetime DEFAULT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `id_admin` int(11) NOT NULL,
  `fecha_pago` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_mensualidades`
--

CREATE TABLE `pagos_mensualidades` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `mes` varchar(20) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `concepto` varchar(255) DEFAULT 'Mensualidad',
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `estado` enum('pendiente','pagado','cancelado') DEFAULT 'pendiente',
  `metodo_pago` varchar(50) DEFAULT 'Efectivo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_detalle`
--

CREATE TABLE `pago_detalle` (
  `id` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `id_mensualidad` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos`
--

CREATE TABLE `periodos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodos`
--

INSERT INTO `periodos` (`id`, `nombre`, `activo`) VALUES
(1, 'I Trimestre 2026', 1),
(2, 'II Trimestre 2026', 1),
(3, 'III Trimestre 2026', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

CREATE TABLE `profesores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `especialidad` varchar(150) DEFAULT NULL,
  `usuario` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_cuenta`
--

CREATE TABLE `solicitudes_cuenta` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(160) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `nombre_estudiante` varchar(160) NOT NULL,
  `grado_estudiante` varchar(40) DEFAULT NULL,
  `id_estudiante` int(11) DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_password`
--

CREATE TABLE `solicitudes_password` (
  `id` int(11) NOT NULL,
  `tipo_usuario` enum('admin','padre') NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_asistencia` (`id_estudiante`,`fecha`);

--
-- Indices de la tabla `avisos`
--
ALTER TABLE `avisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aviso_admin` (`id_admin`);

--
-- Indices de la tabla `conceptos_pago`
--
ALTER TABLE `conceptos_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `conducta`
--
ALTER TABLE `conducta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_conducta_estudiante` (`id_estudiante`),
  ADD KEY `fk_conducta_admin` (`id_admin`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carnet` (`carnet`),
  ADD KEY `fk_estudiante_padre` (`id_padre`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mensualidades`
--
ALTER TABLE `mensualidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_mensualidad` (`id_estudiante`,`id_concepto`,`periodo_mes`),
  ADD KEY `fk_mens_concepto` (`id_concepto`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_nota` (`id_estudiante`,`id_materia`,`id_periodo`),
  ADD KEY `fk_nota_materia` (`id_materia`),
  ADD KEY `fk_nota_periodo` (`id_periodo`);

--
-- Indices de la tabla `padres`
--
ALTER TABLE `padres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `fk_pago_padre` (`id_padre`),
  ADD KEY `fk_pago_admin` (`id_admin`),
  ADD KEY `fk_pago_anulador` (`anulado_por`);

--
-- Indices de la tabla `pagos_mensualidades`
--
ALTER TABLE `pagos_mensualidades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_pago` (`id_pago`),
  ADD KEY `fk_detalle_mensualidad` (`id_mensualidad`),
  ADD KEY `fk_detalle_estudiante` (`id_estudiante`);

--
-- Indices de la tabla `periodos`
--
ALTER TABLE `periodos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `solicitudes_cuenta`
--
ALTER TABLE `solicitudes_cuenta`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `solicitudes_password`
--
ALTER TABLE `solicitudes_password`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `avisos`
--
ALTER TABLE `avisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `conceptos_pago`
--
ALTER TABLE `conceptos_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `conducta`
--
ALTER TABLE `conducta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `mensualidades`
--
ALTER TABLE `mensualidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `padres`
--
ALTER TABLE `padres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_mensualidades`
--
ALTER TABLE `pagos_mensualidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `periodos`
--
ALTER TABLE `periodos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `solicitudes_cuenta`
--
ALTER TABLE `solicitudes_cuenta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudes_password`
--
ALTER TABLE `solicitudes_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asistencia_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `avisos`
--
ALTER TABLE `avisos`
  ADD CONSTRAINT `fk_aviso_admin` FOREIGN KEY (`id_admin`) REFERENCES `administradores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `conducta`
--
ALTER TABLE `conducta`
  ADD CONSTRAINT `fk_conducta_admin` FOREIGN KEY (`id_admin`) REFERENCES `administradores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_conducta_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `fk_estudiante_padre` FOREIGN KEY (`id_padre`) REFERENCES `padres` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensualidades`
--
ALTER TABLE `mensualidades`
  ADD CONSTRAINT `fk_mens_concepto` FOREIGN KEY (`id_concepto`) REFERENCES `conceptos_pago` (`id`),
  ADD CONSTRAINT `fk_mens_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `fk_nota_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nota_materia` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nota_periodo` FOREIGN KEY (`id_periodo`) REFERENCES `periodos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pago_admin` FOREIGN KEY (`id_admin`) REFERENCES `administradores` (`id`),
  ADD CONSTRAINT `fk_pago_anulador` FOREIGN KEY (`anulado_por`) REFERENCES `administradores` (`id`),
  ADD CONSTRAINT `fk_pago_padre` FOREIGN KEY (`id_padre`) REFERENCES `padres` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  ADD CONSTRAINT `fk_detalle_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `fk_detalle_mensualidad` FOREIGN KEY (`id_mensualidad`) REFERENCES `mensualidades` (`id`),
  ADD CONSTRAINT `fk_detalle_pago` FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
