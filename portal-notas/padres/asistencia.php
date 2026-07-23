<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirPadre();

$stmt = $pdo->prepare('SELECT id FROM estudiantes WHERE id_padre = ?');
$stmt->execute([$_SESSION['padre_id']]);
$idsHijos = array_column($stmt->fetchAll(), 'id');

$registros = [];
if ($idsHijos) {
    $in = implode(',', array_fill(0, count($idsHijos), '?'));
    $stmt = $pdo->prepare("
        SELECT a.fecha, a.estado, a.observacion, e.nombre, e.apellido
        FROM asistencia a JOIN estudiantes e ON e.id = a.id_estudiante
        WHERE a.id_estudiante IN ($in)
        ORDER BY a.fecha DESC
    ");
    $stmt->execute($idsHijos);
    $registros = $stmt->fetchAll();
}
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
  <?php include __DIR__ . '/../includes/sidebar_padre.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Asistencia'; include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="panel">
      <table class="tabla-datos">
        <thead><tr><th>Estudiante</th><th>Fecha</th><th>Estado</th><th>Observación</th></tr></thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
          <tr>
            <td><?= h($r['nombre'] . ' ' . $r['apellido']) ?></td>
            <td><?= h(date('d/m/Y', strtotime($r['fecha']))) ?></td>
            <td><span class="badge-estado <?= h($r['estado']) ?>"><?= h($r['estado']) ?></span></td>
            <td><?= h($r['observacion']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$registros): ?>
          <tr><td colspan="4">No hay registros de asistencia todavía.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
