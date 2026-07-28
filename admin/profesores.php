<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

$errores        = [];
$exito          = null;
$modoEdicion    = false;
$profesorEditar = null;

function passwordValida(string $pass): bool {
    // Mínimo 8 caracteres, una mayúscula, una minúscula, un número y un símbolo especial
    return (bool) preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass);
}

/* --- Crear o actualizar profesor --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['accion'] ?? '', ['crear', 'actualizar'], true)) {
    $accion       = $_POST['accion'];
    $id           = $accion === 'actualizar' ? (int)($_POST['id'] ?? 0) : null;
    $nombre       = trim($_POST['nombre'] ?? '');
    $apellido     = trim($_POST['apellido'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $usuario      = trim($_POST['usuario'] ?? '');
    $contrasena   = $_POST['contrasena'] ?? '';

    if ($nombre === '' || $apellido === '' || $email === '' || $usuario === '') {
        $errores[] = 'Por favor completa todos los campos obligatorios.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }

    // La contraseña es obligatoria al crear; opcional al editar (en blanco = no cambiarla)
    if ($accion === 'crear' && $contrasena === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }
    if ($contrasena !== '' && !passwordValida($contrasena)) {
        $errores[] = 'La contraseña debe tener mínimo 8 caracteres e incluir al menos una mayúscula, una minúscula, un número y un símbolo especial.';
    }

    if (!$errores) {
        if ($accion === 'actualizar') {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM profesores WHERE (email = ? OR usuario = ?) AND id != ?');
            $stmt->execute([$email, $usuario, $id]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM profesores WHERE email = ? OR usuario = ?');
            $stmt->execute([$email, $usuario]);
        }
        if ($stmt->fetchColumn() > 0) {
            $errores[] = 'Ya existe un profesor registrado con ese correo o usuario.';
        }
    }

    if (!$errores && $accion === 'crear') {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('
            INSERT INTO profesores (nombre, apellido, email, telefono, especialidad, usuario, contrasena)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$nombre, $apellido, $email, $telefono, $especialidad, $usuario, $hash]);
        header('Location: profesores.php?ok=1');
        exit;
    }

    if (!$errores && $accion === 'actualizar') {
        if ($contrasena !== '') {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('
                UPDATE profesores
                SET nombre = ?, apellido = ?, email = ?, telefono = ?, especialidad = ?, usuario = ?, contrasena = ?
                WHERE id = ?
            ');
            $stmt->execute([$nombre, $apellido, $email, $telefono, $especialidad, $usuario, $hash, $id]);
        } else {
            $stmt = $pdo->prepare('
                UPDATE profesores
                SET nombre = ?, apellido = ?, email = ?, telefono = ?, especialidad = ?, usuario = ?
                WHERE id = ?
            ');
            $stmt->execute([$nombre, $apellido, $email, $telefono, $especialidad, $usuario, $id]);
        }
        header('Location: profesores.php?actualizado=1');
        exit;
    }

    if ($accion === 'actualizar') {
        $modoEdicion    = true;
        $profesorEditar = [
            'id' => $id, 'nombre' => $nombre, 'apellido' => $apellido, 'email' => $email,
            'telefono' => $telefono, 'especialidad' => $especialidad, 'usuario' => $usuario,
        ];
    }
}

/* --- Eliminar profesor --- */
if (isset($_GET['eliminar']) && ctype_digit($_GET['eliminar'])) {
    $stmt = $pdo->prepare('DELETE FROM profesores WHERE id = ?');
    $stmt->execute([$_GET['eliminar']]);
    header('Location: profesores.php?eliminado=1');
    exit;
}

/* --- Cargar profesor a editar (GET) --- */
if (!$modoEdicion && isset($_GET['editar']) && ctype_digit($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT id, nombre, apellido, email, telefono, especialidad, usuario FROM profesores WHERE id = ?');
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

$profesores = $pdo->query('
    SELECT id, nombre, apellido, email, telefono, especialidad, usuario
    FROM profesores
    ORDER BY id DESC
')->fetchAll();

$totalProfesores = count($profesores);

$v = $profesorEditar ?: ['id' => '', 'nombre' => '', 'apellido' => '', 'email' => '', 'telefono' => '', 'especialidad' => '', 'usuario' => ''];
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
        <ul>
          <?php foreach ($errores as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="panel" id="panel-formulario">
      <h2>
        <svg class="lucide" data-lucide="<?= $modoEdicion ? 'pencil' : 'user-plus' ?>"></svg>
        <?= $modoEdicion ? 'Editar profesor' : 'Registrar nuevo profesor' ?>
      </h2>
      <form method="POST" action="profesores.php" class="formulario-profesor">
        <input type="hidden" name="accion" value="<?= $modoEdicion ? 'actualizar' : 'crear' ?>">
        <?php if ($modoEdicion): ?>
          <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
        <?php endif; ?>

        <div class="fila-formulario">
          <div class="campo">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" value="<?= h($v['nombre']) ?>" required>
          </div>
          <div class="campo">
            <label for="apellido">Apellido *</label>
            <input type="text" id="apellido" name="apellido" value="<?= h($v['apellido']) ?>" required>
          </div>
        </div>
        <div class="fila-formulario">
          <div class="campo">
            <label for="email">Correo electrónico *</label>
            <input type="email" id="email" name="email" value="<?= h($v['email']) ?>" required>
          </div>
          <div class="campo">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" value="<?= h($v['telefono']) ?>">
          </div>
        </div>
        <div class="fila-formulario">
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
          <?php if ($modoEdicion): ?>
            <a href="profesores.php" class="btn-secundario">Cancelar edición</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="users"></svg> Profesores registrados</h2>
      <table class="tabla-datos">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Especialidad</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($profesores as $p): ?>
          <tr>
            <td><?= h($p['nombre'] . ' ' . $p['apellido']) ?></td>
            <td><?= h($p['usuario']) ?></td>
            <td><?= h($p['email']) ?></td>
            <td><?= h($p['telefono'] ?: '—') ?></td>
            <td><?= h($p['especialidad'] ?: '—') ?></td>
            <td class="celda-acciones">
              <a href="profesores.php?editar=<?= (int)$p['id'] ?>#panel-formulario" class="btn-editar" title="Editar">
                <svg class="lucide" data-lucide="pencil"></svg>
              </a>
              <a href="profesores.php?eliminar=<?= (int)$p['id'] ?>"
                 class="btn-eliminar"
                 title="Eliminar"
                 onclick="return confirm('¿Eliminar a este profesor?');">
                <svg class="lucide" data-lucide="trash-2"></svg>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$profesores): ?>
          <tr><td colspan="6">Aún no hay profesores registrados.</td></tr>
        <?php endif; ?>
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
.fila-formulario input {
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.12);
  background: rgba(255,255,255,0.05);
  color: inherit;
  font-family: inherit;
  font-size: 14px;
}
.fila-formulario input:focus { outline: none; border-color: #3b82f6; }
.fila-formulario input:invalid:not(:placeholder-shown) { border-color: #ef4444; }
.campo-ayuda { font-size: 12px; opacity: 0.6; }
.acciones-formulario { display: flex; gap: 12px; align-items: center; }
.btn-primario {
  display: inline-flex; align-items: center; gap: 8px;
  background: #3b82f6; color: #fff; border: none;
  padding: 10px 18px; border-radius: 8px; font-weight: 600;
  cursor: pointer; width: fit-content;
}
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
