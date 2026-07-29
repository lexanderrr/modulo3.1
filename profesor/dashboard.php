<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirProfesor();

$stmt = $pdo->prepare('SELECT nombre, correo, especialidad FROM administradores WHERE id = ?');
$stmt->execute([$_SESSION['admin_id']]);
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
  <?php include __DIR__ . '/../includes/sidebar_profesor.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Panel de Profesor'; include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="panel">
      <h2>
        <svg class="lucide" data-lucide="presentation"></svg>
        Bienvenido, <?= h($profesor['nombre'] ?? $_SESSION['admin_nombre'] ?? '') ?>
      </h2>
      <p>Has iniciado sesión correctamente como profesor.</p>
      <?php if (!empty($profesor['especialidad'])): ?>
        <p>Especialidad: <?= h($profesor['especialidad']) ?></p>
      <?php endif; ?>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>