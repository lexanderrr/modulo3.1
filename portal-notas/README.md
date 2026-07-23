# Portal Web de Consulta de Notas para Padres de Familia

Sistema web hecho en **PHP + MySQL**, pensado para correr en **XAMPP**, tal como lo pide
el Módulo 3.1 (Desarrollo de aplicaciones de software).

## 1. Instalación en XAMPP

1. Instala XAMPP e inicia los módulos **Apache** y **MySQL** desde el Panel de Control.
2. Copia la carpeta `portal-notas` completa dentro de `C:\xampp\htdocs\` (o `/opt/lampp/htdocs/` en Linux).
3. Abre `http://localhost/phpmyadmin`, crea la base importando el script:
   `sql/portal_notas.sql` (Importar → seleccionar archivo → Ejecutar).
   Esto crea la base `portal_notas`, todas las tablas y datos de ejemplo.
4. Revisa `config/conexion.php`: por defecto usa usuario `root` y contraseña vacía
   (configuración estándar de XAMPP). Si tu MySQL tiene otra contraseña, cámbiala ahí.
5. Abre `http://localhost/portal-notas/index.php` en el navegador.

## 2. Credenciales de acceso (datos de prueba)

| Rol            | Usuario  | Contraseña  |
|-----------------|----------|-------------|
| Administrador   | `admin`  | `Admin123!` |
| Padre de familia| `mgarcia`| `Padre123!` |
| Padre de familia| `jlopez` | `Padre123!` |

Las contraseñas se guardan **encriptadas con bcrypt** (`password_hash`/`password_verify`
de PHP), nunca en texto plano.

## 3. Estructura del proyecto

```
portal-notas/
├── index.php               Login (padre / administrador)
├── login_procesar.php      Verifica credenciales
├── logout.php              Cierra sesión
├── config/conexion.php     Conexión PDO a MySQL
├── includes/                sesion.php, sidebar_admin.php, sidebar_padre.php
├── admin/                  Panel administrador (dashboard, estudiantes, padres,
│                            materias, notas, asistencia, avisos)
├── padres/                 Panel de padres (dashboard, notas, asistencia, avisos)
├── assets/                 CSS propio + Bootstrap + Font Awesome
└── sql/portal_notas.sql    Script completo de la base de datos + datos de ejemplo
```

## 4. ¿Cumple con lo que pide el módulo?

Comparando contra el descriptor de "Propuestas_Proyecto_Modulo_1.pdf":

| Exigencia del módulo | ¿Cubierto? | Dónde |
|---|---|---|
| Entregable = sitio web (no app móvil), stack XAMPP (Apache+MySQL+PHP) | **Sí** | Todo el sistema está en PHP puro con PDO/MySQL |
| Login diferenciado por rol (padre / administrador) | **Sí** | `index.php`, `login_procesar.php`, sesiones separadas |
| Registro de estudiantes, materias y períodos | **Sí** | `admin/estudiantes.php`, `admin/materias.php`, período dentro de `admin/notas.php` |
| Ingreso de notas y asistencia por el administrador | **Sí** | `admin/notas.php`, `admin/asistencia.php` |
| Consulta de notas y asistencia por el padre (solo lectura, solo sus hijos) | **Sí** | `padres/notas.php`, `padres/asistencia.php` (consultas filtradas por `id_padre`) |
| Módulo de avisos/comunicados | **Sí** | `admin/avisos.php` (publicar) y `padres/avisos.php` (consultar) |
| Base de datos con modelo lógico → físico | **Sí** | `sql/portal_notas.sql`: 7 tablas relacionadas con llaves foráneas |
| Sentencias SQL de inserción, actualización y consulta | **Sí** | Uso de `INSERT`, `UPDATE`, `SELECT` con JOIN en todas las páginas, vía PDO preparado |
| Codificación con estándar y comentarios | **Sí** | Nombres consistentes, comentarios en cabeceras de archivos clave |
| Arquitectura cliente-servidor / MVC simple | **Parcial** | El sistema separa datos (config/SQL), lógica (PHP) y presentación (HTML/CSS), pero no usa un framework MVC formal — es válido para el nivel del módulo |

**Lo que este sistema te entrega:** requerimientos base cubiertos, base de datos,
código funcional y una interfaz cuidada. **Lo que TÚ y tu equipo deben entregar además**
(porque son documentos, no código, y el módulo los pide por separado):

- Documento de visión / levantamiento de requerimientos (entrevistas a 2-3 usuarios reales).
- Diagramas UML (casos de uso y clases) — pueden basarse en las tablas de `portal_notas.sql`.
- Diagrama entidad-relación formal (puedes exportarlo desde phpMyAdmin: pestaña "Diseñador").
- Plan de pruebas con evidencias (capturas de pantalla, login incorrecto, notas fuera de rango, etc.).
- Plan de implementación y documento de entrega al cliente.
- Manual de usuario breve.

En otras palabras: **el sistema (código) cumple técnicamente con lo que pide el módulo**,
pero la nota final del módulo también depende de la documentación del ciclo de vida
(requerimientos, UML, pruebas, entrega) que ustedes deben redactar como equipo — el código
por sí solo no reemplaza esos entregables.

## 5. Seguridad y buenas prácticas ya incluidas

- Contraseñas con hash bcrypt (no texto plano).
- Consultas con **sentencias preparadas PDO** (protección contra inyección SQL).
- Escape de salida con `htmlspecialchars` (protección básica contra XSS).
- Validación de rango de notas (0–10) en base de datos (`CHECK`) y en PHP.
- Padres solo pueden ver datos de **sus propios hijos** (filtrado por `id_padre` en cada consulta).
- Rutas protegidas: `exigirAdmin()` / `exigirPadre()` bloquean acceso sin sesión.

## 6. Notas para el video/demo o entrega

Para la demo puedes usar el flujo: admin ingresa notas y asistencia → publica un aviso →
cierra sesión → padre inicia sesión → revisa notas, asistencia y avisos de su hijo.
