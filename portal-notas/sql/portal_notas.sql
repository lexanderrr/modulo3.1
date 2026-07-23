-- =========================================================
-- Portal Web de Consulta de Notas para Padres de Familia
-- Base de datos: portal_notas
-- Motor: MySQL / MariaDB (compatible con XAMPP)
-- =========================================================

CREATE DATABASE IF NOT EXISTS portal_notas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE portal_notas;

-- ---------------------------------------------------------
-- Tabla: administradores (docentes / secretaría)
-- ---------------------------------------------------------
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    correo VARCHAR(120),
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: padres
-- ---------------------------------------------------------
CREATE TABLE padres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    correo VARCHAR(120),
    telefono VARCHAR(20),
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: estudiantes
-- ---------------------------------------------------------
CREATE TABLE estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carnet VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    grado VARCHAR(40),
    seccion VARCHAR(10),
    fecha_nacimiento DATE,
    id_padre INT NOT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudiante_padre FOREIGN KEY (id_padre)
        REFERENCES padres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: materias
-- ---------------------------------------------------------
CREATE TABLE materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    docente VARCHAR(100),
    grado VARCHAR(40)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: periodos (trimestres / bimestres)
-- ---------------------------------------------------------
CREATE TABLE periodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: notas
-- ---------------------------------------------------------
CREATE TABLE notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_materia INT NOT NULL,
    id_periodo INT NOT NULL,
    nota DECIMAL(4,2) NOT NULL,
    comentario VARCHAR(255),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nota_estudiante FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id) ON DELETE CASCADE,
    CONSTRAINT fk_nota_materia FOREIGN KEY (id_materia) REFERENCES materias(id) ON DELETE CASCADE,
    CONSTRAINT fk_nota_periodo FOREIGN KEY (id_periodo) REFERENCES periodos(id) ON DELETE CASCADE,
    CONSTRAINT uq_nota UNIQUE (id_estudiante, id_materia, id_periodo),
    CONSTRAINT chk_nota_rango CHECK (nota >= 0 AND nota <= 10)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: asistencia
-- ---------------------------------------------------------
CREATE TABLE asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('Presente','Ausente','Tardanza') NOT NULL DEFAULT 'Presente',
    observacion VARCHAR(255),
    CONSTRAINT fk_asistencia_estudiante FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id) ON DELETE CASCADE,
    CONSTRAINT uq_asistencia UNIQUE (id_estudiante, fecha)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: avisos
-- ---------------------------------------------------------
CREATE TABLE avisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    contenido TEXT NOT NULL,
    id_admin INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aviso_admin FOREIGN KEY (id_admin) REFERENCES administradores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- DATOS DE PRUEBA
-- =========================================================

-- Administrador. Usuario: admin  |  Contraseña: Admin123!
INSERT INTO administradores (nombre, usuario, password, correo) VALUES
('Secretaría Académica', 'admin', '$2y$10$I2tCazIvrLhLAIZ3gK0F/.YhfoSQ9clCS25v8KxTjagonp9JGb8MS', 'secretaria@instituto.edu.sv');

-- Padres de familia. Usuario: mgarcia / jlopez | Contraseña (ambos): Padre123!
INSERT INTO padres (nombre, apellido, correo, telefono, usuario, password) VALUES
('María', 'García', 'maria.garcia@correo.com', '7000-1111', 'mgarcia', '$2y$10$x6RHvUKq0/eicCWsfzV/FeQvPFCM61lZOJGhSLbGnCgFSuUwNGu/S'),
('José', 'López', 'jose.lopez@correo.com', '7000-2222', 'jlopez', '$2y$10$x6RHvUKq0/eicCWsfzV/FeQvPFCM61lZOJGhSLbGnCgFSuUwNGu/S');

-- Estudiantes
INSERT INTO estudiantes (carnet, nombre, apellido, grado, seccion, fecha_nacimiento, id_padre) VALUES
('2026-001', 'Ana', 'García', '2do Año Bachillerato', 'A', '2009-03-12', 1),
('2026-002', 'Carlos', 'García', '1er Año Bachillerato', 'B', '2010-07-25', 1),
('2026-003', 'Sofía', 'López', '3er Año Bachillerato', 'A', '2008-11-05', 2);

-- Materias
INSERT INTO materias (nombre, docente, grado) VALUES
('Matemática', 'Lic. Pedro Ramírez', '2do Año Bachillerato'),
('Lenguaje y Literatura', 'Licda. Carmen Ruiz', '2do Año Bachillerato'),
('Programación Web', 'Ing. Luis Hernández', '2do Año Bachillerato'),
('Inglés', 'Licda. Ana Beltrán', '1er Año Bachillerato'),
('Física', 'Ing. Marta Solís', '3er Año Bachillerato');

-- Períodos
INSERT INTO periodos (nombre, activo) VALUES
('I Trimestre 2026', 1),
('II Trimestre 2026', 1),
('III Trimestre 2026', 0);

-- Notas de ejemplo
INSERT INTO notas (id_estudiante, id_materia, id_periodo, nota, comentario) VALUES
(1, 1, 1, 8.50, 'Buen desempeño en exámenes'),
(1, 2, 1, 9.00, NULL),
(1, 3, 1, 9.50, 'Excelente proyecto final'),
(2, 4, 1, 7.80, NULL),
(3, 5, 1, 8.20, 'Necesita reforzar laboratorio');

-- Asistencia de ejemplo
INSERT INTO asistencia (id_estudiante, fecha, estado, observacion) VALUES
(1, '2026-07-13', 'Presente', NULL),
(1, '2026-07-14', 'Tardanza', 'Llegó 15 min tarde'),
(2, '2026-07-13', 'Ausente', 'Cita médica'),
(3, '2026-07-13', 'Presente', NULL);

-- Avisos de ejemplo
INSERT INTO avisos (titulo, contenido, id_admin) VALUES
('Entrega de boletines', 'El próximo viernes se entregarán los boletines del I Trimestre en la dirección del instituto.', 1),
('Reunión de padres de familia', 'Se convoca a reunión general el sábado 25 de julio a las 9:00 a.m. en el auditorio.', 1);
