<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

// Solo administradores reales pueden gestionar profesores
if (($_SESSION['admin_rol'] ?? '') !== 'admin') {
    header('Location: ../profesor/dashboard.php');
    exit;
}

$errores        = [];
$exito          = null;
$modoEdicion    = false;
$profesorEditar = null;

function passwordValida(string $pass): bool {
    return (bool) preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass);
}

/* --- Crear o actualizar profesor --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['accion'] ?? '', ['crear', 'actualizar'], true)) {
    $accion       = $_POST['accion'];
    $id           = $accion === 'actualizar' ? (int)($_POST['id'] ?? 0) : null;
    $nombre       = trim($_POST['nombre'] ?? '');
    $correo       = trim($_POST['correo'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $usuario      = trim($_POST['usuario'] ?? '');
    $contrasena   = $_POST['contrasena'] ?? '';

    if ($nombre === '' || $correo === '' || $usuario === '') {
        $errores[] = 'Por favor completa todos los campos obligatorios.';
    }
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }
    if ($accion === 'crear' && $contrasena === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }
    if ($contrasena !== '' && !passwordValida($contrasena)) {
        $errores[] = 'La contraseña debe tener mínimo 8 caracteres e incluir al menos una mayúscula, una minúscula, un número y un símbolo especial.';
    }

    if (!$errores) {
        if ($accion === 'actualizar') {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM administradores WHERE (usuario = ? OR correo = ?) AND id != ?');
            $stmt->execute([$usuario, $correo, $id]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM administradores WHERE usuario = ? OR correo = ?');
            $stmt->execute([$usuario, $correo]);
        }
        if ($stmt->fetchColumn() > 0) {
            $errores[] = 'Ya existe una cuenta (profesor o administrador) con ese usuario o correo.';
        }
    }

    if (!$errores && $accion === 'crear') {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO administradores (nombre, usuario, password, correo, telefono, especialidad, rol, creado_en)
            VALUES (?, ?, ?, ?, ?, ?, 'profesor', NOW())
        ");
        $stmt->execute([$nombre, $usuario, $hash, $correo, $telefono, $especialidad]);
        header('Location: profesores.php?ok=1');
        exit;
    }

    if (!$errores && $accion === 'actualizar') {
        if ($contrasena !== '') {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE administradores
                SET nombre = ?, usuario = ?, correo = ?, telefono = ?, especialidad = ?, password = ?
                WHERE id = ? AND rol = 'profesor'
            ");
            $stmt->execute([$nombre, $usuario, $correo, $telefono, $especialidad, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE administradores
                SET nombre = ?, usuario = ?, correo = ?, telefono = ?, especialidad = ?
                WHERE id = ? AND rol = 'profesor'
            ");
            $stmt->execute([$nombre, $usuario, $correo, $telefono, $especialidad, $id]);
        }
        header('Location: profesores.php?actualizado=1');
        exit;
    }

    if ($accion === 'actualizar') {
        $modoEdicion    = true;
        $profesorEditar = [
            'id' => $id, 'nombre' => $nombre, 'correo' => $correo,
            'telefono' => $telefono, 'especialidad' => $especialidad, 'usuario' => $usuario,
        ];
    }
}

/* --- Eliminar (nunca toca cuentas con rol = admin) --- */
if (isset($_GET['eliminar']) && ctype_digit($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM administradores WHERE id = ? AND rol = 'profesor'");
    $stmt->execute([$_GET['eliminar']]);
    header('Location: profesores.php?eliminado=1');
    exit;
}

/* --- Cargar profesor a editar --- */
if (!$modoEdicion && isset($_GET['editar']) && ctype_digit($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT id, nombre, usuario, correo, telefono, especialidad FROM administradores WHERE id = ? AND rol = 'profesor'");
    $stmt->execute([$_GET['editar']]);
    $fila = $stmt->fetch();
    if ($fila) {
        $modoEdicion    = true;
        $profesorEditar = $fila;
    }
}

if (isset($_GET['ok']))          { $exito = 'Profesor registrado correctamente.'; }
if (isset($_GET['actualizado'])) { $exito = 'Profesor actualizado correctamente.'; }
if (isset($_GET['eliminado']))   { $exito = 'Profesor eliminado correctamente.'; }

$profesores = $pdo->query("
    SELECT id, nombre, usuario, correo, telefono, especialidad, creado_en
    FROM administradores
    WHERE rol = 'profesor'
    ORDER BY creado_en DESC
")->fetchAll();

$totalProfesores = count($profesores);
$v = $profesorEditar ?: ['id' => '', 'nombre' => '', 'correo' => '', 'telefono' => '', 'especialidad' => '', 'usuario' => ''];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Profesores | Portal de Notas</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="fondo-ambiental"></div>
<div class="app-shell">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Profesores'; include __DIR__ . '/../includes/topbar.php'; ?>

    <div class="grid-tarjetas">
      <div class="tarjeta-stat">
        <div class="icono bg-azul"><svg class="lucide" data-lucide="briefcase"></svg></div>
        <div class="num"><?= (int)$totalProfesores ?></div>
        <div class="label">Profesores registrados</div>
      </div>
    </div>

    <?php if ($exito): ?>
      <div class="alerta alerta-ok"><?= h($exito) ?></div>
    <?php endif; ?>
    <?php if ($errores): ?>
      <div class="alerta alerta-error">
        <ul><?php foreach ($errores as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="panel" id="panel-formulario">
      <h2>
        <svg class="lucide" data-lucide="<?= $modoEdicion ? 'pencil' : 'user-plus' ?>"></svg>
        <?= $modoEdicion ? 'Editar profesor' : 'Registrar nuevo profesor' ?>
      </h2>
      <form method="POST" action="profesores.php" class="formulario-profesor">
        <input type="hidden" name="accion" value="<?= $modoEdicion ? 'actualizar' : 'crear' ?>">
        <?php if ($modoEdicion): ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><?php endif; ?>

        <div class="fila-formulario">
          <div class="campo">
            <label for="nombre">Nombre completo *</label>
            <input type="text" id="nombre" name="nombre" value="<?= h($v['nombre']) ?>" required>
          </div>
          <div class="campo">
            <label for="correo">Correo electrónico *</label>
            <input type="email" id="correo" name="correo" value="<?= h($v['correo']) ?>" required>
          </div>
        </div>
        <div class="fila-formulario">
          <div class="campo">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" value="<?= h($v['telefono']) ?>">
          </div>
          <div class="campo">
            <label for="especialidad">Especialidad / Materia</label>
            <input type="text" id="especialidad" name="especialidad" value="<?= h($v['especialidad']) ?>" placeholder="Ej. Matemática, Física...">
          </div>
        </div>
        <div class="fila-formulario">
          <div class="campo">
            <label for="usuario">Usuario *</label>
            <input type="text" id="usuario" name="usuario" value="<?= h($v['usuario']) ?>" required>
          </div>
          <div class="campo">
            <label for="contrasena">Contraseña <?= $modoEdicion ? '(dejar en blanco para no cambiar)' : '*' ?> </label>
            <input type="password" id="contrasena" name="contrasena" <?= !$modoEdicion ? 'required' : '' ?> placeholder="Min. 8 caracteres, mayús, minús, número y símbolo">
          </div>
        </div>
        <div style="margin-top: 16px;">
          <button type="submit" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> <?= $modoEdicion ? 'Actualizar profesor' : 'Registrar profesor' ?></button>
          <?php if ($modoEdicion): ?>
            <a href="profesores.php" class="btn-secundario"><svg class="lucide" data-lucide="x"></svg> Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="users"></svg> Listado de profesores (<?= $totalProfesores ?>)</h2>
      <table class="tabla-datos">
        <thead>
          <tr><th>Nombre</th><th>Usuario</th><th>Correo</th><th>Teléfono</th><th>Especialidad</th><th>Registrado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($profesores as $prof): ?>
          <tr>
            <td><?= h($prof['nombre']) ?></td>
            <td><code><?= h($prof['usuario']) ?></code></td>
            <td><?= h($prof['correo']) ?></td>
            <td><?= h($prof['telefono'] ?: '—') ?></td>
            <td><?= h($prof['especialidad'] ?: '—') ?></td>
            <td><?= h(date('d/m/Y', strtotime($prof['creado_en']))) ?></td>
            <td>
              <a class="btn-sm btn-editar" href="profesores.php?editar=<?= (int)$prof['id'] ?>" title="Editar"><svg class="lucide" data-lucide="pencil"></svg></a>
              <a class="btn-sm btn-eliminar" href="profesores.php?eliminar=<?= (int)$prof['id'] ?>" title="Eliminar" onclick="return confirm('¿Eliminar este profesor?');"><svg class="lucide" data-lucide="trash-2"></svg></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$profesores): ?>
          <tr><td colspan="7">No hay profesores registrados.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
<script>
  lucide.createIcons();
  document.addEventListener('DOMContentLoaded', function() {
    const formProf = document.querySelector('.formulario-profesor');
    formProf?.addEventListener('submit', function(e) {
      const pass = document.getElementById('contrasena');
      if (pass && pass.value && pass.value.length < 8) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 8 caracteres.');
      }
    });
  });
</script>
</body>
</html>
