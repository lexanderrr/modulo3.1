<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEstudiante = (int)$_POST['id_estudiante'];
    $fecha        = $_POST['fecha'];
    $estado       = $_POST['estado'];
    $observacion  = trim($_POST['observacion']);

    if ($idEstudiante && $fecha && in_array($estado, ['Presente', 'Ausente', 'Tardanza'], true)) {
        $stmt = $pdo->prepare('
            INSERT INTO asistencia (id_estudiante, fecha, estado, observacion)
            VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE estado = VALUES(estado), observacion = VALUES(observacion)
        ');
        $stmt->execute([$idEstudiante, $fecha, $estado, $observacion ?: null]);
    }
    header('Location: asistencia.php?ok=1');
    exit;
}

if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare('DELETE FROM asistencia WHERE id = ?');
    $stmt->execute([(int)$_GET['eliminar']]);
    header('Location: asistencia.php?ok=1');
    exit;
}

$estudiantes = $pdo->query('SELECT id, nombre, apellido, carnet FROM estudiantes ORDER BY apellido, nombre')->fetchAll();

$registros = $pdo->query('
    SELECT a.id, e.nombre, e.apellido, a.fecha, a.estado, a.observacion
    FROM asistencia a JOIN estudiantes e ON e.id = a.id_estudiante
    ORDER BY a.fecha DESC LIMIT 100
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Asistencia | Portal de Notas</title>
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
    <?php $tituloPagina = 'Control de Asistencia'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Cambios guardados correctamente.</div>
    <?php endif; ?>

    <div class="panel">
      <h2>Registrar asistencia</h2>
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
        <div class="form-grupo"><label>Fecha</label><input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required></div>
        <div class="form-grupo">
          <label>Estado</label>
          <select name="estado" style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;">
            <option value="Presente">Presente</option>
            <option value="Ausente">Ausente</option>
            <option value="Tardanza">Tardanza</option>
          </select>
        </div>
        <div class="form-grupo"><label>Observación</label><input type="text" name="observacion"></div>
        <div class="form-grupo"><button type="submit" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> Guardar</button></div>
      </form>
    </div>

    <div class="panel">
      <h2>Últimos registros</h2>
      <table class="tabla-datos">
        <thead><tr><th>Estudiante</th><th>Fecha</th><th>Estado</th><th>Observación</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
          <tr>
            <td><?= h($r['nombre'] . ' ' . $r['apellido']) ?></td>
            <td><?= h(date('d/m/Y', strtotime($r['fecha']))) ?></td>
            <td><span class="badge-estado <?= h($r['estado']) ?>"><?= h($r['estado']) ?></span></td>
            <td><?= h($r['observacion']) ?></td>
            <td><a class="btn-sm btn-eliminar" href="asistencia.php?eliminar=<?= $r['id'] ?>" onclick="return confirm('¿Eliminar este registro?');"><svg class="lucide" data-lucide="trash-2"></svg></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$registros): ?>
          <tr><td colspan="5">No hay registros de asistencia.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
