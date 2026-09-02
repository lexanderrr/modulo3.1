<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

// Solo administradores reales pueden acceder a este módulo (no profesores ni cajeros)
if (($_SESSION['admin_rol'] ?? '') !== 'admin') {
    header('Location: ../profesor/dashboard.php');
    exit;
}

$totalEstudiantes = $pdo->query('SELECT COUNT(*) FROM estudiantes')->fetchColumn();
$totalPadres      = $pdo->query('SELECT COUNT(*) FROM padres')->fetchColumn();
$totalMaterias    = $pdo->query('SELECT COUNT(*) FROM materias')->fetchColumn();
$promedioGeneral  = $pdo->query('SELECT ROUND(AVG(nota),2) FROM notas')->fetchColumn();
$totalSolicitudesPendientes =
    (int)$pdo->query("SELECT COUNT(*) FROM solicitudes_cuenta WHERE estado = 'pendiente'")->fetchColumn() +
    (int)$pdo->query("SELECT COUNT(*) FROM solicitudes_password WHERE estado = 'pendiente'")->fetchColumn();

$ultimosAvisos = $pdo->query('SELECT titulo, fecha FROM avisos ORDER BY fecha DESC LIMIT 4')->fetchAll();
$ultimasNotas = $pdo->query('
    SELECT e.nombre, e.apellido, m.nombre AS materia, n.nota, n.fecha_registro
    FROM notas n
    JOIN estudiantes e ON e.id = n.id_estudiante
    JOIN materias m ON m.id = n.id_materia
    ORDER BY n.fecha_registro DESC LIMIT 6
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Administrador | Portal de Notas</title>
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
    <?php $tituloPagina = 'Panel de Administración'; include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="grid-tarjetas">
      <div class="tarjeta-stat">
        <div class="icono bg-azul"><svg class="lucide" data-lucide="graduation-cap"></svg></div>
        <div class="num"><?= (int)$totalEstudiantes ?></div>
        <div class="label">Estudiantes registrados</div>
      </div>
      <div class="tarjeta-stat">
        <div class="icono bg-dorado"><svg class="lucide" data-lucide="users"></svg></div>
        <div class="num"><?= (int)$totalPadres ?></div>
        <div class="label">Padres de familia</div>
      </div>
      <div class="tarjeta-stat">
        <div class="icono bg-verde"><svg class="lucide" data-lucide="book-open"></svg></div>
        <div class="num"><?= (int)$totalMaterias ?></div>
        <div class="label">Materias activas</div>
      </div>
      <div class="tarjeta-stat">
        <div class="icono bg-rojo"><svg class="lucide" data-lucide="star"></svg></div>
        <div class="num"><?= $promedioGeneral !== null ? h($promedioGeneral) : '—' ?></div>
        <div class="label">Promedio general</div>
      </div>
      <div class="tarjeta-stat">
        <div class="icono bg-dorado"><svg class="lucide" data-lucide="inbox"></svg></div>
        <div class="num"><a href="solicitudes.php" style="color:inherit; text-decoration:none;"><?= $totalSolicitudesPendientes ?></a></div>
        <div class="label"><a href="solicitudes.php" style="color:inherit; text-decoration:none;">Solicitudes pendientes</a></div>
      </div>
        </div>

   
    <div class="panel">
      <h2>
        <svg class="lucide" data-lucide="star"></svg>
        Últimas notas registradas
      </h2>
      <table class="tabla-datos">
        <thead><tr><th>Estudiante</th><th>Materia</th><th>Nota</th><th>Fecha</th></tr></thead>
        <tbody>
        <?php foreach ($ultimasNotas as $n): ?>
          <tr>
            <td><?= h($n['nombre'] . ' ' . $n['apellido']) ?></td>
            <td><?= h($n['materia']) ?></td>
            <td><span class="badge-nota <?= $n['nota'] >= 6 ? 'aprobado' : 'reprobado' ?>"><?= h($n['nota']) ?></span></td>
            <td><?= h(date('d/m/Y', strtotime($n['fecha_registro']))) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$ultimasNotas): ?>
          <tr><td colspan="4">Aún no hay notas registradas.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="megaphone"></svg> Últimos avisos publicados</h2>
      <?php foreach ($ultimosAvisos as $a): ?>
        <div class="aviso-card">
          <div class="fecha"><?= h(date('d/m/Y', strtotime($a['fecha']))) ?></div>
          <h3><?= h($a['titulo']) ?></h3>
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
