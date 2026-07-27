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

// Registrar o actualizar nota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_nota'])) {
    $idEstudiante = (int)$_POST['id_estudiante'];
    $idMateria    = (int)$_POST['id_materia'];
    $idPeriodo    = (int)$_POST['id_periodo'];
    $nota         = (float)str_replace(',', '.', $_POST['nota']);
    $comentario   = trim($_POST['comentario'] ?? '');

    // Verificar que la materia pertenezca al profesor
    if (!in_array($idMateria, $idsMaterias)) {
        $mensaje = 'No puedes registrar notas en una materia que no te pertenece.';
    } elseif ($nota < 0 || $nota > 10) {
        $mensaje = 'La nota debe estar entre 0 y 10.';
    } elseif (!$idEstudiante || !$idPeriodo) {
        $mensaje = 'Selecciona estudiante y período.';
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO notas (id_estudiante, id_materia, id_periodo, nota, comentario)
            VALUES (?,?,?,?,?)
            ON DUPLICATE KEY UPDATE nota = VALUES(nota), comentario = VALUES(comentario), fecha_registro = NOW()
        ');
        $stmt->execute([$idEstudiante, $idMateria, $idPeriodo, $nota, $comentario ?: null]);
        header('Location: notas.php?ok=nota');
        exit;
    }
}

if (isset($_GET['eliminar'])) {
    $idNota = (int)$_GET['eliminar'];
    // Verificar que la nota pertenezca a una materia del profesor
    $stmt = $pdo->prepare('SELECT id_materia FROM notas WHERE id = ?');
    $stmt->execute([$idNota]);
    $notaCheck = $stmt->fetch();
    if ($notaCheck && in_array($notaCheck['id_materia'], $idsMaterias)) {
        $stmt = $pdo->prepare('DELETE FROM notas WHERE id = ?');
        $stmt->execute([$idNota]);
    }
    header('Location: notas.php?ok=eliminado');
    exit;
}

// Obtener estudiantes para el select (los que tienen notas en materias del profesor)
$estudiantesSelect = [];
if ($idsMaterias) {
    $in = implode(',', array_fill(0, count($idsMaterias), '?'));
    $stmt = $pdo->prepare("
        SELECT DISTINCT e.id, e.nombre, e.apellido, e.carnet
        FROM estudiantes e
        JOIN notas n ON n.id_estudiante = e.id
        WHERE n.id_materia IN ($in)
        ORDER BY e.apellido, e.nombre
    ");
    $stmt->execute($idsMaterias);
    $estudiantesSelect = $stmt->fetchAll();

    if (!$estudiantesSelect && $misMaterias) {
        // Si no hay notas aún, mostrar todos los estudiantes del grado
        $grados = array_unique(array_column($misMaterias, 'grado'));
        $inG = implode(',', array_fill(0, count($grados), '?'));
        $stmt = $pdo->prepare("SELECT id, nombre, apellido, carnet FROM estudiantes WHERE grado IN ($inG) ORDER BY apellido, nombre");
        $stmt->execute(array_values($grados));
        $estudiantesSelect = $stmt->fetchAll();
    }
}

$periodos = $pdo->query('SELECT id, nombre FROM periodos ORDER BY id DESC')->fetchAll();

// Listado de notas filtrado por materia del profesor
$filtroPeriodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 0;
$filtroMateria = isset($_GET['materia']) ? (int)$_GET['materia'] : 0;

$notas = [];
if ($idsMaterias) {
    $in = implode(',', array_fill(0, count($idsMaterias), '?'));
    $sql = "
        SELECT n.id, e.nombre, e.apellido, e.carnet, m.nombre AS materia, p.nombre AS periodo, n.nota, n.comentario, n.id_materia
        FROM notas n
        JOIN estudiantes e ON e.id = n.id_estudiante
        JOIN materias m ON m.id = n.id_materia
        JOIN periodos p ON p.id = n.id_periodo
        WHERE n.id_materia IN ($in)
    ";
    $params = $idsMaterias;
    if ($filtroPeriodo) { $sql .= ' AND n.id_periodo = ?'; $params[] = $filtroPeriodo; }
    if ($filtroMateria) { $sql .= ' AND n.id_materia = ?'; $params[] = $filtroMateria; }
    $sql .= ' ORDER BY e.apellido, e.nombre, m.nombre';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $notas = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Calificaciones | Portal de Notas</title>
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
    <?php $tituloPagina = 'Gestión de Calificaciones'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Cambios guardados correctamente.</div>
    <?php endif; ?>
    <?php if ($mensaje): ?>
      <div class="alerta alerta-error"><svg class="lucide" data-lucide="alert-circle"></svg> <?= h($mensaje) ?></div>
    <?php endif; ?>

    <div class="panel">
      <h2>Registrar / actualizar calificación</h2>
      <?php if (!$misMaterias): ?>
        <div class="alerta alerta-error">No tienes materias asignadas.</div>
      <?php endif; ?>
      <form method="POST" class="form-inline-grid">
        <input type="hidden" name="guardar_nota" value="1">
        <div class="form-grupo">
          <label>Estudiante</label>
          <select name="id_estudiante" required style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="">-- Selecciona --</option>
            <?php foreach ($estudiantesSelect as $e): ?>
              <option value="<?= $e['id'] ?>"><?= h($e['nombre'] . ' ' . $e['apellido'] . ' (' . $e['carnet'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label>Materia</label>
          <select name="id_materia" required style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="">-- Selecciona --</option>
            <?php foreach ($misMaterias as $m): ?>
              <option value="<?= $m['id'] ?>"><?= h($m['nombre']) ?> (<?= h($m['grado']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label>Período</label>
          <select name="id_periodo" required style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="">-- Selecciona --</option>
            <?php foreach ($periodos as $p): ?>
              <option value="<?= $p['id'] ?>"><?= h($p['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Nota (0 - 10)</label><input type="number" step="0.01" min="0" max="10" name="nota" required></div>
        <div class="form-grupo"><label>Comentario</label><input type="text" name="comentario"></div>
        <div class="form-grupo"><button type="submit" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> Guardar</button></div>
      </form>
    </div>

    <div class="panel">
      <h2>Calificaciones registradas</h2>
      <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div style="flex:1;min-width:160px;">
          <select name="materia" style="width:100%;padding:9px 12px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="0">Todas las materias</option>
            <?php foreach ($misMaterias as $m): ?>
              <option value="<?= $m['id'] ?>" <?= $filtroMateria == $m['id'] ? 'selected' : '' ?>><?= h($m['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:1;min-width:160px;">
          <select name="periodo" style="width:100%;padding:9px 12px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="0">Todos los períodos</option>
            <?php foreach ($periodos as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $filtroPeriodo == $p['id'] ? 'selected' : '' ?>><?= h($p['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn-primario" style="width:auto;padding:9px 18px;"><svg class="lucide" data-lucide="filter"></svg> Filtrar</button>
      </form>
      <div class="tabla-wrap">
        <table class="tabla-datos">
          <thead><tr><th>Estudiante</th><th>Materia</th><th>Período</th><th>Nota</th><th>Comentario</th><th>Acc.</th></tr></thead>
          <tbody>
          <?php foreach ($notas as $n): ?>
            <tr>
              <td><?= h($n['nombre'] . ' ' . $n['apellido']) ?></td>
              <td><?= h($n['materia']) ?></td>
              <td><?= h($n['periodo']) ?></td>
              <td><span class="badge-nota <?= $n['nota'] >= 6 ? 'aprobado' : 'reprobado' ?>"><?= h($n['nota']) ?></span></td>
              <td><?= h($n['comentario']) ?></td>
              <td><a class="btn-sm btn-eliminar" href="notas.php?eliminar=<?= $n['id'] ?>" onclick="return confirm('¿Eliminar esta calificación?');"><svg class="lucide" data-lucide="trash-2"></svg></a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$notas): ?>
            <tr><td colspan="6">No hay calificaciones registradas.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
