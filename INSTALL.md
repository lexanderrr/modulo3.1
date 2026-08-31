# Guía de Instalación - SIGEA

**SIGEA** - Sistema de Gestión Educativa Académica

---

## 📋 Requisitos Previos

Antes de instalar SIGEA, asegúrate de tener:

- **PHP 7.4 o superior**
- **MySQL 5.7 o superior** (o MariaDB 10.3+)
- **XAMPP** (o similar: WAMP, LAMP)
- **Git** instalado
- **Navegador web** moderno (Chrome, Firefox, Edge)


## Configurar Conexión a BD

### 3.1 Editar archivo de configuración

1. Abre: `C:\xampp\htdocs\modulo3.1\config\conexion.php`
2. Verifica o actualiza los datos:

```php
<?php
$servidor = "localhost";
$usuario = "root";
$contrasena = "";  // Vacío por defecto en XAMPP
$basedatos = "sigea";

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $contrasena, $basedatos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>
```

3. Guarda el archivo

---

## 🌐 Iniciar Servidor

###  Iniciar XAMPP

1. Abre **XAMPP Control Panel**
2. Click en **Start** para:
   - Apache
   - MySQL

###  Verificar que esté corriendo

- Apache debe mostrar **Running** (en verde)
- MySQL debe mostrar **Running** (en verde)

---

## ✅ Acceder a SIGEA

1. Abre tu navegador
2. Ve a: `http://localhost/modulo3.1`
3. Deberías ver la pantalla de **Login**

---

## 🔧 Configuración Inicial

### 7.1 En el Panel Admin

1. Login como **Admin**
2. Ve a **Configuración**
3. Actualiza:
   - Nombre de la institución
   - Correo de contacto
   - Logo/Imagen
   - Datos del director

### Crear Usuarios

1. Ve a **Gestión de Usuarios**
2. Crea:
   - Nuevos profesores
   - Nuevos padres de familia
   - Nuevos estudiantes

### Crear Materias

1. Ve a **Materias**
2. Agrega:
   - Nombre de la materia
   - Código
   - Profesor asignado
   - Descripción

---

## 📁 Estructura de Carpetas

```
modulo3.1/
├── admin/              # Panel de administrador
├── profesor/           # Módulo para profesores
├── padres/             # Portal para padres
├── assets/
│   ├── css/            # Hojas de estilo
│   └── js/             # Scripts JavaScript
├── config/
│   └── conexion.php    # Configuración de BD
├── includes/           # Archivos compartidos
│   ├── sesion.php
│   ├── topbar.php
│   └── sidebar_*.php
├── Manuales/           # Documentación
├── index.php           # Login principal
└── README.md
```

---

## 🐛 Solución de Problemas

### Error: "No se puede conectar a la base de datos"

1. Verifica que MySQL esté corriendo
2. Comprueba las credenciales en `config/conexion.php`
3. Asegúrate que la BD `sigea` exista

### Error: "Página no encontrada"

1. Verifica que Apache esté corriendo
2. La URL debe ser: `http://localhost/modulo3.1`
3. Comprueba que los archivos estén en `C:\xampp\htdocs\modulo3.1`

### Error: "No puedo hacer login"

1. Verifica que ingresaste correctamente el usuario y contraseña
2. Comprueba que la BD se importó correctamente
3. Limpia cache del navegador (Ctrl + Shift + Supr)

### Error: "Archivos de sesión"

1. Ve a `C:\xampp\php\php.ini`
2. Busca: `session.save_path`
3. Asegúrate de tener permisos en esa carpeta

---

## 📖 Documentación Adicional

Consulta los manuales en la carpeta `/Manuales/`:

- **MANUAL DE USUARIO - SIGEA.pdf** - Guía completa del sistema
- **MANUAL TECNICO - SIGEA.pdf** - Documentación técnica
- **Manual de Codigo.pdf** - Referencia de código

---

## 🔐 Recomendaciones de Seguridad

1. **Cambia las contraseñas por defecto** inmediatamente
2. **Usa HTTPS** en producción
3. **Haz copias de seguridad** regularmente
4. **Restringe acceso** a archivos de configuración
5. **Valida entrada de datos** constantemente

---

## 📞 Soporte

Si tienes problemas:

1. Consulta el **Manual de Usuario**
2. Revisa el **Manual Técnico**
3. Verifica los **Requerimientos**
4. Contacta al equipo de desarrollo

---


Disfruta usando **SIGEA** - Sistema de Gestión Educativa Académica

**Última actualización:** Agosto 2026
