<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirPadre();

$avisos = $pdo->query('SELECT titulo, contenido, fecha FROM avisos ORDER BY fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Avisos | Portal de Notas</title>
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
    <?php $tituloPagina = 'Avisos del Instituto'; include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="panel">
      <?php foreach ($avisos as $a): ?>
        <div class="aviso-card">
          <div class="fecha"><?= h(date('d/m/Y H:i', strtotime($a['fecha']))) ?></div>
          <h3><?= h($a['titulo']) ?></h3>
          <p style="margin:0;color:var(--gris-texto);"><?= nl2br(h($a['contenido'])) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (!$avisos): ?>
        <p>No hay avisos publicados todavía.</p>
      <?php endif; ?>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
