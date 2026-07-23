<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

$mensaje = '';

// Crear nuevo período
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_periodo'])) {
    $nombrePeriodo = trim($_POST['nombre_periodo']);
    if ($nombrePeriodo) {
        $stmt = $pdo->prepare('INSERT INTO periodos (nombre, activo) VALUES (?, 1)');
        $stmt->execute([$nombrePeriodo]);
    }
    header('Location: notas.php?ok=periodo');
    exit;
}

// Registrar o actualizar nota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_nota'])) {
    $idEstudiante = (int)$_POST['id_estudiante'];
    $idMateria    = (int)$_POST['id_materia'];
    $idPeriodo    = (int)$_POST['id_periodo'];
    $nota         = (float)str_replace(',', '.', $_POST['nota']);
    $comentario   = trim($_POST['comentario']);

    if ($nota < 0 || $nota > 10) {
        $mensaje = 'La nota debe estar entre 0 y 10.';
    } elseif (!$idEstudiante || !$idMateria || !$idPeriodo) {
        $mensaje = 'Selecciona estudiante, materia y período.';
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
    $stmt = $pdo->prepare('DELETE FROM notas WHERE id = ?');
    $stmt->execute([(int)$_GET['eliminar']]);
    header('Location: notas.php?ok=eliminado');
    exit;
}

$estudiantes = $pdo->query('SELECT id, nombre, apellido, carnet FROM estudiantes ORDER BY apellido, nombre')->fetchAll();
$materias    = $pdo->query('SELECT id, nombre FROM materias ORDER BY nombre')->fetchAll();
$periodos    = $pdo->query('SELECT id, nombre FROM periodos ORDER BY id DESC')->fetchAll();

$filtroPeriodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 0;
$sql = '
    SELECT n.id, e.nombre, e.apellido, e.carnet, m.nombre AS materia, p.nombre AS periodo, n.nota, n.comentario
    FROM notas n
    JOIN estudiantes e ON e.id = n.id_estudiante
    JOIN materias m ON m.id = n.id_materia
    JOIN periodos p ON p.id = n.id_periodo
';
$params = [];
if ($filtroPeriodo) { $sql .= ' WHERE n.id_periodo = ?'; $params[] = $filtroPeriodo; }
$sql .= ' ORDER BY e.apellido, e.nombre';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Notas | Portal de Notas</title>
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
    <?php $tituloPagina = 'Ingreso de Calificaciones'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Cambios guardados correctamente.</div>
    <?php endif; ?>
    <?php if ($mensaje): ?>
      <div class="alerta alerta-error"><svg class="lucide" data-lucide="alert-circle"></svg> <?= h($mensaje) ?></div>
    <?php endif; ?>

    <div class="panel">
      <h2>Nuevo período académico</h2>
      <form method="POST" class="form-inline-grid">
        <input type="hidden" name="nuevo_periodo" value="1">
        <div class="form-grupo"><label>Nombre del período</label><input type="text" name="nombre_periodo" placeholder="Ej. II Trimestre 2026" required></div>
        <div class="form-grupo"><button type="submit" class="btn-primario"><svg class="lucide" data-lucide="plus"></svg> Crear período</button></div>
      </form>
    </div>

    <div class="panel">
      <h2>Registrar / actualizar nota</h2>
      <form method="POST" class="form-inline-grid">
        <input type="hidden" name="guardar_nota" value="1">
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
          <label>Materia</label>
          <select name="id_materia" required style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="">-- Selecciona --</option>
            <?php foreach ($materias as $m): ?>
              <option value="<?= $m['id'] ?>"><?= h($m['nombre']) ?></option>
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
        <div class="form-grupo"><label>Comentario (opcional)</label><input type="text" name="comentario"></div>
        <div class="form-grupo"><button type="submit" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> Guardar nota</button></div>
      </form>
    </div>

    <div class="panel">
      <h2>Notas registradas</h2>
      <form method="GET" style="margin-bottom:16px;max-width:280px;">
        <select name="periodo" onchange="this.form.submit()" style="width:100%;padding:9px 12px;border:1.5px solid var(--borde);border-radius:10px;">
          <option value="0">Todos los períodos</option>
          <?php foreach ($periodos as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $filtroPeriodo == $p['id'] ? 'selected' : '' ?>><?= h($p['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
      <table class="tabla-datos">
        <thead><tr><th>Estudiante</th><th>Materia</th><th>Período</th><th>Nota</th><th>Comentario</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($notas as $n): ?>
          <tr>
            <td><?= h($n['nombre'] . ' ' . $n['apellido']) ?></td>
            <td><?= h($n['materia']) ?></td>
            <td><?= h($n['periodo']) ?></td>
            <td><span class="badge-nota <?= $n['nota'] >= 6 ? 'aprobado' : 'reprobado' ?>"><?= h($n['nota']) ?></span></td>
            <td><?= h($n['comentario']) ?></td>
            <td><a class="btn-sm btn-eliminar" href="notas.php?eliminar=<?= $n['id'] ?>" onclick="return confirm('¿Eliminar esta nota?');"><svg class="lucide" data-lucide="trash-2"></svg></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$notas): ?>
          <tr><td colspan="6">No hay notas registradas.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
