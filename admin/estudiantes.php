<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

$mensaje = '';

// Eliminar
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare('DELETE FROM estudiantes WHERE id = ?');
    $stmt->execute([(int)$_GET['eliminar']]);
    header('Location: estudiantes.php?ok=eliminado');
    exit;
}

// Crear o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $carnet   = trim($_POST['carnet']);
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $grado    = trim($_POST['grado']);
    $seccion  = trim($_POST['seccion']);
    $nacimiento = $_POST['fecha_nacimiento'] ?: null;
    $idPadre  = (int)$_POST['id_padre'];

    if ($carnet && $nombre && $apellido && $idPadre) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE estudiantes SET carnet=?, nombre=?, apellido=?, grado=?, seccion=?, fecha_nacimiento=?, id_padre=? WHERE id=?');
            $stmt->execute([$carnet, $nombre, $apellido, $grado, $seccion, $nacimiento, $idPadre, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO estudiantes (carnet, nombre, apellido, grado, seccion, fecha_nacimiento, id_padre) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$carnet, $nombre, $apellido, $grado, $seccion, $nacimiento, $idPadre]);
        }
        header('Location: estudiantes.php?ok=guardado');
        exit;
    } else {
        $mensaje = 'Completa los campos obligatorios (carnet, nombre, apellido y padre).';
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT * FROM estudiantes WHERE id = ?');
    $stmt->execute([(int)$_GET['editar']]);
    $editar = $stmt->fetch();
}

$padresDisponibles = $pdo->query('SELECT id, nombre, apellido FROM padres ORDER BY nombre')->fetchAll();
$estudiantes = $pdo->query('
    SELECT e.*, p.nombre AS padre_nombre, p.apellido AS padre_apellido
    FROM estudiantes e JOIN padres p ON p.id = e.id_padre
    ORDER BY e.apellido, e.nombre
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estudiantes | Portal de Notas</title>
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
    <?php $tituloPagina = 'Gestión de Estudiantes'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Cambios guardados correctamente.</div>
    <?php endif; ?>
    <?php if ($mensaje): ?>
      <div class="alerta alerta-error"><svg class="lucide" data-lucide="alert-circle"></svg> <?= h($mensaje) ?></div>
    <?php endif; ?>

    <div class="panel">
      <h2><?= $editar ? 'Editar estudiante' : 'Registrar nuevo estudiante' ?></h2>
      <form method="POST">
        <input type="hidden" name="id" value="<?= h($editar['id'] ?? '') ?>">
        <div class="form-inline-grid">
          <div class="form-grupo"><label>Carnet</label><input type="text" name="carnet" value="<?= h($editar['carnet'] ?? '') ?>" required></div>
          <div class="form-grupo"><label>Nombre</label><input type="text" name="nombre" value="<?= h($editar['nombre'] ?? '') ?>" required></div>
          <div class="form-grupo"><label>Apellido</label><input type="text" name="apellido" value="<?= h($editar['apellido'] ?? '') ?>" required></div>
          <div class="form-grupo"><label>Grado</label><input type="text" name="grado" value="<?= h($editar['grado'] ?? '') ?>"></div>
          <div class="form-grupo"><label>Sección</label><input type="text" name="seccion" value="<?= h($editar['seccion'] ?? '') ?>"></div>
          <div class="form-grupo"><label>Fecha de nacimiento</label><input type="date" name="fecha_nacimiento" value="<?= h($editar['fecha_nacimiento'] ?? '') ?>"></div>
          <div class="form-grupo">
            <label>Padre/Madre responsable</label>
            <select name="id_padre" required style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;">
              <option value="">-- Selecciona --</option>
              <?php foreach ($padresDisponibles as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (isset($editar['id_padre']) && $editar['id_padre'] == $p['id']) ? 'selected' : '' ?>>
                  <?= h($p['nombre'] . ' ' . $p['apellido']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo"><button type="submit" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> Guardar</button></div>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2>Listado de estudiantes</h2>
      <table class="tabla-datos">
        <thead><tr><th>Carnet</th><th>Nombre</th><th>Grado</th><th>Sección</th><th>Padre/Madre</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($estudiantes as $e): ?>
          <tr>
            <td><?= h($e['carnet']) ?></td>
            <td><?= h($e['nombre'] . ' ' . $e['apellido']) ?></td>
            <td><?= h($e['grado']) ?></td>
            <td><?= h($e['seccion']) ?></td>
            <td><?= h($e['padre_nombre'] . ' ' . $e['padre_apellido']) ?></td>
            <td>
              <a class="btn-sm btn-editar" href="estudiantes.php?editar=<?= $e['id'] ?>"><svg class="lucide" data-lucide="pencil"></svg></a>
              <a class="btn-sm btn-eliminar" href="estudiantes.php?eliminar=<?= $e['id'] ?>" onclick="return confirm('¿Eliminar este estudiante?');"><svg class="lucide" data-lucide="trash-2"></svg></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$estudiantes): ?>
          <tr><td colspan="6">No hay estudiantes registrados.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
