# SIGEA — Sistema de Gestión Escolar y Académica

Portal web para la administración académica de un centro educativo: control de notas, asistencia, conducta, pagos de mensualidad y comunicación entre administración, profesores y padres de familia.

Desarrollado en **PHP** con **MySQL/MariaDB**, sin frameworks ni dependencias externas — pensado para desplegarse fácilmente sobre XAMPP.

---

## 📋 Tabla de contenido

- [Características](#-características)
- [Roles del sistema](#-roles-del-sistema)
- [Tecnologías](#-tecnologías)
- [Estructura del proyecto](#-estructura-del-proyecto)
- [Requisitos previos](#-requisitos-previos)
- [Instalación](#-instalación)
- [Uso](#-uso)
- [Documentación](#-documentación)
- [Seguridad](#-seguridad)
- [Contribuir](#-contribuir)
- [Licencia](#-licencia)

---

## ✨ Características

- **Gestión de estudiantes, materias y profesores** desde el panel administrativo.
- **Registro de notas** por materia y período, con boleta de calificaciones descargable en PDF.
- **Control de asistencia** diaria por profesor y consulta en tiempo real para los padres.
- **Registro de conducta** de cada estudiante.
- **Módulo de pagos de mensualidad**: generación de recibos con correlativo único, historial y anulación con motivo (append-only, nunca se eliminan registros).
- **Tablero de avisos** para comunicar información a padres y profesores.
- **Solicitudes de cuenta y recuperación de contraseña** para padres, gestionadas por administración.
- **Autenticación por roles** (administrador, cajero, profesor, padre) con sesiones seguras.

## 👥 Roles del sistema

| Rol | Accesos principales |
|---|---|
| **Administrador** | Gestión completa: estudiantes, profesores, padres, materias, notas, asistencia, avisos, solicitudes de cuenta |
| **Cajero** | Registro y anulación de pagos de mensualidad |
| **Profesor** | Registro de notas, asistencia y conducta de sus estudiantes; publicación de avisos |
| **Padre/Tutor** | Consulta de notas, asistencia y avisos de sus hijos; descarga de boleta en PDF |

## 🛠 Tecnologías

- **Backend:** PHP (PDO + prepared statements)
- **Base de datos:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript vanilla
- **Iconografía:** [Lucide Icons](https://lucide.dev/)
- **Tipografía:** Google Fonts (Inter)
- **Entorno recomendado:** XAMPP (Apache + MySQL + PHP)

## 📁 Estructura del proyecto

```
├── admin/                  # Panel de administración
│   ├── dashboard.php
│   ├── estudiantes.php
│   ├── profesores.php
│   ├── padres.php
│   ├── materias.php
│   ├── notas.php
│   ├── asistencia.php
│   ├── pagos.php
│   ├── recibo_pago.php
│   └── avisos.php
├── profesor/                # Panel del profesor
│   ├── dashboard.php
│   ├── estudiantes.php
│   ├── notas.php
│   ├── asistencia.php
│   ├── conducta.php
│   └── avisos.php
├── padres/                  # Panel del padre/tutor
│   ├── dashboard.php
│   ├── notas.php
│   ├── asistencia.php
│   └── avisos.php
├── includes/                # Componentes y lógica compartida
│   ├── sesion.php           # Autenticación y control de acceso por rol
│   ├── sidebar_admin.php
│   ├── sidebar_profesor.php
│   ├── sidebar_padre.php
│   └── topbar.php
├── config/
│   └── conexion.php         # Conexión PDO a la base de datos
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── Manuales/                 # Documentación técnica y de usuario
│   ├── MANUAL TECNICO - SIGEA.pdf
│   ├── MANUAL DE USUARIO - SIGEA.pdf
│   └── Manual de Codigo.pdf
├── index.php                 # Login
├── login_procesar.php
└── logout.php
```

## ✅ Requisitos previos

- [XAMPP](https://www.apachefriends.org/) (o Apache + PHP 8+ + MySQL/MariaDB por separado)
- PHP 8.0 o superior
- MySQL 5.7+ / MariaDB 10.3+

## 📖 Uso

Ingresa con las credenciales correspondientes a tu rol desde la pantalla de login. Cada rol es redirigido automáticamente a su panel:

- Administrador / Cajero → `admin/dashboard.php`
- Profesor → `profesor/dashboard.php`
- Padre/Tutor → `padres/dashboard.php`

Los padres sin cuenta pueden generar una **solicitud de registro** desde el login, la cual queda pendiente de aprobación por parte de un administrador.

## 📚 Documentación

Este repositorio incluye documentación completa dentro de la carpeta [`Manuales/`](./Manuales):

- **Manual Técnico**: arquitectura, base de datos y despliegue.
- **Manual de Usuario**: guía de uso para cada rol del sistema.
- **Manual de Código**: convenciones y estructura del código fuente.

## 🔒 Seguridad

- Contraseñas almacenadas con `password_hash()` / verificadas con `password_verify()`.
- Consultas parametrizadas mediante **PDO** (prevención de inyección SQL).
- Escape de salida HTML mediante la función `h()` (prevención de XSS).
- Control de acceso por rol en cada módulo (`exigirAdmin()`, `exigirProfesor()`, `exigirPadre()`, `exigirCajero()`).
- Los pagos son de tipo *append-only*: nunca se eliminan, solo se anulan con motivo registrado.

> ⚠️ Este proyecto está pensado para uso académico/local. Antes de un despliegue en producción, se recomienda añadir HTTPS, variables de entorno para credenciales, protección CSRF y límites de intentos de login.

