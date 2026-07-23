<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirPadre();

$stmt = $pdo->prepare('SELECT id, nombre, apellido FROM estudiantes WHERE id_padre = ? ORDER BY nombre');
$stmt->execute([$_SESSION['padre_id']]);
$hijos = $stmt->fetchAll();
$idsHijos = array_column($hijos, 'id');

$notas = [];
if ($idsHijos) {
    $in = implode(',', array_fill(0, count($idsHijos), '?'));
    $stmt = $pdo->prepare("
        SELECT n.nota, n.comentario, e.nombre, e.apellido, m.nombre AS materia, p.nombre AS periodo, n.fecha_registro
        FROM notas n
        JOIN estudiantes e ON e.id = n.id_estudiante
        JOIN materias m ON m.id = n.id_materia
        JOIN periodos p ON p.id = n.id_periodo
        WHERE n.id_estudiante IN ($in)
        ORDER BY e.nombre, p.id DESC, m.nombre
    ");
    $stmt->execute($idsHijos);
    $notas = $stmt->fetchAll();
}
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
  <?php include __DIR__ . '/../includes/sidebar_padre.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Calificaciones'; include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="panel">
      <table class="tabla-datos">
        <thead><tr><th>Estudiante</th><th>Materia</th><th>Período</th><th>Nota</th><th>Comentario</th></tr></thead>
        <tbody>
        <?php foreach ($notas as $n): ?>
          <tr>
            <td><?= h($n['nombre'] . ' ' . $n['apellido']) ?></td>
            <td><?= h($n['materia']) ?></td>
            <td><?= h($n['periodo']) ?></td>
            <td><span class="badge-nota <?= $n['nota'] >= 6 ? 'aprobado' : 'reprobado' ?>"><?= h($n['nota']) ?></span></td>
            <td><?= h($n['comentario']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$notas): ?>
          <tr><td colspan="5">Aún no hay notas registradas para tus hijos.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
