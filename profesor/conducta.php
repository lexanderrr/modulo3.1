<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

if ($_SESSION['admin_rol'] !== 'profesor') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$mensaje = '';

// Obtener materias del profesor
$stmt = $pdo->prepare('SELECT * FROM materias WHERE docente LIKE ? ORDER BY nombre');
$stmt->execute(['%' . $_SESSION['admin_nombre'] . '%']);
$misMaterias = $stmt->fetchAll();
$idsMaterias = array_column($misMaterias, 'id');

// Obtener estudiantes
$estudiantes = [];
if ($idsMaterias) {
    $in = implode(',', array_fill(0, count($idsMaterias), '?'));
    $stmt = $pdo->prepare("
        SELECT DISTINCT e.id, e.carnet, e.nombre, e.apellido, e.grado, e.seccion
        FROM estudiantes e
        JOIN notas n ON n.id_estudiante = e.id
        WHERE n.id_materia IN ($in)
        ORDER BY e.apellido, e.nombre
    ");
    $stmt->execute($idsMaterias);
    $estudiantes = $stmt->fetchAll();

    if (!$estudiantes) {
        $grados = array_unique(array_column($misMaterias, 'grado'));
        if ($grados) {
            $inG = implode(',', array_fill(0, count($grados), '?'));
            $stmt = $pdo->prepare("SELECT id, carnet, nombre, apellido, grado, seccion FROM estudiantes WHERE grado IN ($inG) ORDER BY apellido, nombre");
            $stmt->execute(array_values($grados));
            $estudiantes = $stmt->fetchAll();
        }
    }
}

// Guardar registro de conducta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $idEstudiante = (int)$_POST['id_estudiante'];
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $descripcion = trim($_POST['descripcion']);
    $tipo = $_POST['tipo'];

    if ($idEstudiante && $descripcion && in_array($tipo, ['Positiva', 'Negativa', 'Neutra'])) {
        $stmt = $pdo->prepare('INSERT INTO conducta (id_estudiante, fecha, descripcion, tipo, id_admin) VALUES (?,?,?,?,?)');
        $stmt->execute([$idEstudiante, $fecha, $descripcion, $tipo, $_SESSION['admin_id']]);
        header('Location: conducta.php?ok=1');
        exit;
    } else {
        $mensaje = 'Completa todos los campos obligatorios.';
    }
}

if (isset($_GET['eliminar'])) {
    $idCond = (int)$_GET['eliminar'];
    $stmt = $pdo->prepare('DELETE FROM conducta WHERE id = ? AND id_admin = ?');
    $stmt->execute([$idCond, $_SESSION['admin_id']]);
    header('Location: conducta.php?ok=eliminado');
    exit;
}

$registros = [];
if ($estudiantes) {
    $idsEst = array_column($estudiantes, 'id');
    $inE = implode(',', array_fill(0, count($idsEst), '?'));
    $stmt = $pdo->prepare("
        SELECT c.*, e.nombre, e.apellido, e.carnet
        FROM conducta c
        JOIN estudiantes e ON e.id = c.id_estudiante
        WHERE c.id_estudiante IN ($inE)
        ORDER BY c.fecha DESC, c.creado_en DESC
        LIMIT 100
    ");
    $stmt->execute($idsEst);
    $registros = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Conducta | Portal de Notas</title>
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
  <?php include __DIR__ . '/../includes/sidebar_profesor.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Registro de Conducta'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Registro guardado correctamente.</div>
    <?php endif; ?>
    <?php if ($mensaje): ?>
      <div class="alerta alerta-error"><svg class="lucide" data-lucide="alert-circle"></svg> <?= h($mensaje) ?></div>
    <?php endif; ?>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="shield"></svg> Nuevo registro de conducta</h2>
      <form method="POST" class="form-inline-grid">
        <div class="form-grupo">
          <label>Estudiante</label>
          <select name="id_estudiante" required style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="">-- Selecciona --</option>
            <?php foreach ($estudiantes as $e): ?>
              <option value="<?= $e['id'] ?>"><?= h($e['nombre'] . ' ' . $e['apellido'] . ' (' . $e['carnet'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label>Fecha</label>
          <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-grupo">
          <label>Tipo</label>
          <select name="tipo" style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="Positiva">Positiva 🌟</option>
            <option value="Neutra">Neutra ⚪</option>
            <option value="Negativa">Negativa ⚠️</option>
          </select>
        </div>
        <div class="form-grupo" style="grid-column:1/-1;">
          <label>Descripción del comportamiento</label>
          <textarea name="descripcion" rows="3" required style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;font-family:inherit;"></textarea>
        </div>
        <div class="form-grupo">
          <button type="submit" name="guardar" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> Guardar registro</button>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="list"></svg> Historial de conducta</h2>
      <div class="tabla-wrap">
        <table class="tabla-datos">
          <thead><tr><th>Estudiante</th><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Acc.</th></tr></thead>
          <tbody>
          <?php foreach ($registros as $r): ?>
            <tr>
              <td><?= h($r['nombre'] . ' ' . $r['apellido']) ?></td>
              <td><?= h(date('d/m/Y', strtotime($r['fecha']))) ?></td>
              <td>
                <?php if ($r['tipo'] === 'Positiva'): ?>
                  <span style="color:var(--verde);font-weight:600;">🌟 Positiva</span>
                <?php elseif ($r['tipo'] === 'Negativa'): ?>
                  <span style="color:var(--rojo);font-weight:600;">⚠️ Negativa</span>
                <?php else: ?>
                  <span style="color:var(--gris-texto);">⚪ Neutra</span>
                <?php endif; ?>
              </td>
              <td><?= h($r['descripcion']) ?></td>
              <td><a class="btn-sm btn-eliminar" href="conducta.php?eliminar=<?= $r['id'] ?>" onclick="return confirm('¿Eliminar este registro?');"><svg class="lucide" data-lucide="trash-2"></svg></a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$registros): ?>
            <tr><td colspan="5">No hay registros de conducta.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
