<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirProfesor();

$stmt = $pdo->prepare('SELECT nombre, apellido, email, especialidad FROM profesores WHERE id = ?');
$stmt->execute([$_SESSION['profesor_id']]);
$profesor = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Profesor | Portal de Notas</title>
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
  <aside class="sidebar">
    <div class="marca">
      <div class="icono">PN</div>
      <div class="texto">Portal de Notas<span>Panel Profesor</span></div>
    </div>
    <nav>
      <div class="grupo-titulo">General</div>
      <a href="dashboard.php" class="activo">
        <svg class="lucide" data-lucide="layout-grid"></svg><span class="etiqueta">Inicio</span>
      </a>
      <div class="cerrar-sesion">
        <a href="../logout.php"><svg class="lucide" data-lucide="log-out"></svg><span class="etiqueta">Cerrar sesión</span></a>
      </div>
    </nav>
  </aside>
  <main class="contenido">
    <div class="panel">
      <h2>
        <svg class="lucide" data-lucide="presentation"></svg>
        Bienvenido, <?= h(($profesor['nombre'] ?? '') . ' ' . ($profesor['apellido'] ?? '')) ?>
      </h2>
      <p>Has iniciado sesión correctamente como profesor.</p>
      <?php if (!empty($profesor['especialidad'])): ?>
        <p>Especialidad: <?= h($profesor['especialidad']) ?></p>
      <?php endif; ?>
      <p style="opacity:.7;font-size:14px;margin-top:16px;">
        Este panel es un punto de partida. Cuéntame qué necesita ver o hacer aquí un profesor
        (registrar notas, ver sus materias asignadas, tomar asistencia, etc.) y lo construimos.
      </p>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
