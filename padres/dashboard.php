<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirPadre();

$stmt = $pdo->prepare('SELECT * FROM estudiantes WHERE id_padre = ? ORDER BY nombre');
$stmt->execute([$_SESSION['padre_id']]);
$hijos = $stmt->fetchAll();

$idsHijos = array_column($hijos, 'id');
$promedios = [];
if ($idsHijos) {
    $in = implode(',', array_fill(0, count($idsHijos), '?'));
    $stmt = $pdo->prepare("SELECT id_estudiante, ROUND(AVG(nota),2) AS promedio FROM notas WHERE id_estudiante IN ($in) GROUP BY id_estudiante");
    $stmt->execute($idsHijos);
    foreach ($stmt->fetchAll() as $row) { $promedios[$row['id_estudiante']] = $row['promedio']; }
}

$ultimosAvisos = $pdo->query('SELECT titulo, contenido, fecha FROM avisos ORDER BY fecha DESC LIMIT 3')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Padres | Portal de Notas</title>
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
    <?php $tituloPagina = 'Bienvenido(a), ' . $_SESSION['padre_nombre']; include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="grid-tarjetas">
      <?php foreach ($hijos as $hijo): ?>
        <div class="tarjeta-stat">
          <div class="icono bg-azul"><svg class="lucide" data-lucide="graduation-cap"></svg></div>
          <div class="num"><?= isset($promedios[$hijo['id']]) ? h($promedios[$hijo['id']]) : '—' ?></div>
          <div class="label"><?= h($hijo['nombre'] . ' ' . $hijo['apellido']) ?> · Promedio general</div>
        </div>
      <?php endforeach; ?>
      <?php if (!$hijos): ?>
        <div class="tarjeta-stat"><div class="label">No tienes estudiantes vinculados a tu cuenta todavía.</div></div>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="graduation-cap"></svg> Mis hijos</h2>
      <table class="tabla-datos">
        <thead><tr><th>Nombre</th><th>Carnet</th><th>Grado</th><th>Sección</th></tr></thead>
        <tbody>
        <?php foreach ($hijos as $hijo): ?>
          <tr>
            <td><?= h($hijo['nombre'] . ' ' . $hijo['apellido']) ?></td>
            <td><?= h($hijo['carnet']) ?></td>
            <td><?= h($hijo['grado']) ?></td>
            <td><?= h($hijo['seccion']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="megaphone"></svg> Avisos recientes del instituto</h2>
      <?php foreach ($ultimosAvisos as $a): ?>
        <div class="aviso-card">
          <div class="fecha"><?= h(date('d/m/Y', strtotime($a['fecha']))) ?></div>
          <h3><?= h($a['titulo']) ?></h3>
          <p style="margin:0;color:var(--gris-texto);"><?= nl2br(h($a['contenido'])) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (!$ultimosAvisos): ?>
        <p>No hay avisos publicados todavía.</p>
      <?php endif; ?>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
