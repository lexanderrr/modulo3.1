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
      <div class="alerta alerta-exito"><?= h($exito) ?></div>
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
            <label for="contrasena"><?= $modoEdicion ? 'Nueva contraseña' : 'Contraseña *' ?></label>
            <input type="password" id="contrasena" name="contrasena"
                   <?= $modoEdicion ? '' : 'required' ?>
                   pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                   title="Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo especial"
                   placeholder="<?= $modoEdicion ? 'Dejar en blanco para no cambiarla' : '' ?>">
            <span class="campo-ayuda">Mínimo 8 caracteres: mayúscula, minúscula, número y símbolo especial.</span>
          </div>
        </div>

        <div class="acciones-formulario">
          <button type="submit" class="btn-primario">
            <svg class="lucide" data-lucide="<?= $modoEdicion ? 'save' : 'user-plus' ?>"></svg>
            <?= $modoEdicion ? 'Guardar cambios' : 'Registrar profesor' ?>
          </button>
          <?php if ($modoEdicion): ?><a href="profesores.php" class="btn-secundario">Cancelar edición</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="users"></svg> Profesores registrados</h2>
      <table class="tabla-datos">
        <thead><tr><th>Nombre</th><th>Usuario</th><th>Correo</th><th>Teléfono</th><th>Especialidad</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($profesores as $p): ?>
          <tr>
            <td><?= h($p['nombre']) ?></td>
            <td><?= h($p['usuario']) ?></td>
            <td><?= h($p['correo']) ?></td>
            <td><?= h($p['telefono'] ?: '—') ?></td>
            <td><?= h($p['especialidad'] ?: '—') ?></td>
            <td class="celda-acciones">
              <a href="profesores.php?editar=<?= (int)$p['id'] ?>#panel-formulario" class="btn-editar" title="Editar"><svg class="lucide" data-lucide="pencil"></svg></a>
              <a href="profesores.php?eliminar=<?= (int)$p['id'] ?>" class="btn-eliminar" title="Eliminar" onclick="return confirm('¿Eliminar a este profesor?');"><svg class="lucide" data-lucide="trash-2"></svg></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$profesores): ?><tr><td colspan="6">Aún no hay profesores registrados.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<style>
.formulario-profesor { display: flex; flex-direction: column; gap: 16px; margin-top: 12px; }
.fila-formulario { display: flex; gap: 20px; flex-wrap: wrap; }
.fila-formulario .campo { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 6px; }
.fila-formulario label { font-size: 13px; opacity: 0.8; }
.fila-formulario input { padding: 10px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.05); color: inherit; font-family: inherit; font-size: 14px; }
.fila-formulario input:focus { outline: none; border-color: #3b82f6; }
.campo-ayuda { font-size: 12px; opacity: 0.6; }
.acciones-formulario { display: flex; gap: 12px; align-items: center; }
.btn-primario { display: inline-flex; align-items: center; gap: 8px; background: #3b82f6; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; width: fit-content; }
.btn-primario:hover { background: #2563eb; }
.btn-secundario { color: inherit; opacity: 0.75; text-decoration: underline; font-size: 14px; }
.celda-acciones { display: flex; gap: 14px; }
.btn-editar { color: #3b82f6; }
.btn-eliminar { color: #ef4444; }
.alerta { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
.alerta-exito { background: rgba(34,197,94,0.15); color: #22c55e; }
.alerta-error { background: rgba(239,68,68,0.15); color: #ef4444; }
.alerta-error ul { margin: 0; padding-left: 18px; }
</style>

<script src="../assets/js/app.js"></script>
</body>
</html>