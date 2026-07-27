<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

// Verificar que sea profesor
if ($_SESSION['admin_rol'] !== 'profesor') {
    header('Location: ../admin/dashboard.php');
    exit;
}

// Obtener materias que imparte este profesor
$stmt = $pdo->prepare('SELECT * FROM materias WHERE docente LIKE ? ORDER BY nombre');
$stmt->execute(['%' . $_SESSION['admin_nombre'] . '%']);
$misMaterias = $stmt->fetchAll();

// Obtener IDs de materias
$idsMaterias = array_column($misMaterias, 'id');

// Contar estudiantes en estas materias (a través de notas)
$totalEstudiantes = 0;
$totalNotas = 0;
$promedioGeneral = null;
if ($idsMaterias) {
    $in = implode(',', array_fill(0, count($idsMaterias), '?'));
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT id_estudiante) FROM notas WHERE id_materia IN ($in)");
    $stmt->execute($idsMaterias);
    $totalEstudiantes = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notas WHERE id_materia IN ($in)");
    $stmt->execute($idsMaterias);
    $totalNotas = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT ROUND(AVG(nota),2) FROM notas WHERE id_materia IN ($in)");
    $stmt->execute($idsMaterias);
    $promedioGeneral = $stmt->fetchColumn();
}

$totalMaterias = count($misMaterias);

// Últimas notas registradas
$ultimasNotas = [];
if ($idsMaterias) {
    $in = implode(',', array_fill(0, count($idsMaterias), '?'));
    $stmt = $pdo->prepare("
        SELECT e.nombre, e.apellido, e.carnet, m.nombre AS materia, n.nota, n.fecha_registro
        FROM notas n
        JOIN estudiantes e ON e.id = n.id_estudiante
        JOIN materias m ON m.id = n.id_materia
        WHERE n.id_materia IN ($in)
        ORDER BY n.fecha_registro DESC LIMIT 6
    ");
    $stmt->execute($idsMaterias);
    $ultimasNotas = $stmt->fetchAll();
}

$ultimosAvisos = $pdo->query('SELECT titulo, fecha FROM avisos ORDER BY fecha DESC LIMIT 4')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del Profesor | Portal de Notas</title>
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
    <?php $tituloPagina = 'Panel del Profesor — ' . h($_SESSION['admin_nombre']); include __DIR__ . '/../includes/topbar.php'; ?>
    
    <div class="grid-tarjetas">
      <div class="tarjeta-stat">
        <div class="icono bg-azul"><svg class="lucide" data-lucide="graduation-cap"></svg></div>
        <div class="num"><?= (int)$totalEstudiantes ?></div>
        <div class="label">Estudiantes en mis clases</div>
      </div>
      <div class="tarjeta-stat">
        <div class="icono bg-dorado"><svg class="lucide" data-lucide="book-open"></svg></div>
        <div class="num"><?= (int)$totalMaterias ?></div>
        <div class="label">Materias que imparto</div>
      </div>
      <div class="tarjeta-stat">
        <div class="icono bg-verde"><svg class="lucide" data-lucide="star"></svg></div>
        <div class="num"><?= (int)$totalNotas ?></div>
        <div class="label">Calificaciones registradas</div>
      </div>
      <div class="tarjeta-stat">
        <div class="icono bg-rojo"><svg class="lucide" data-lucide="trending-up"></svg></div>
        <div class="num"><?= $promedioGeneral !== null ? h($promedioGeneral) : '—' ?></div>
        <div class="label">Promedio general de mis clases</div>
      </div>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="book-open"></svg> Mis materias</h2>
      <?php if ($misMaterias): ?>
        <div class="tabla-wrap">
          <table class="tabla-datos">
            <thead><tr><th>Materia</th><th>Grado</th></tr></thead>
            <tbody>
            <?php foreach ($misMaterias as $m): ?>
              <tr>
                <td><?= h($m['nombre']) ?></td>
                <td><?= h($m['grado']) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p style="color:var(--gris-texto);">No tienes materias asignadas. Contacta a la secretaría.</p>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="star"></svg> Últimas calificaciones registradas</h2>
      <div class="tabla-wrap">
        <table class="tabla-datos">
          <thead><tr><th>Estudiante</th><th>Carnet</th><th>Materia</th><th>Nota</th><th>Fecha</th></tr></thead>
          <tbody>
          <?php foreach ($ultimasNotas as $n): ?>
            <tr>
              <td><?= h($n['nombre'] . ' ' . $n['apellido']) ?></td>
              <td><?= h($n['carnet']) ?></td>
              <td><?= h($n['materia']) ?></td>
              <td><span class="badge-nota <?= $n['nota'] >= 6 ? 'aprobado' : 'reprobado' ?>"><?= h($n['nota']) ?></span></td>
              <td><?= h(date('d/m/Y', strtotime($n['fecha_registro']))) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$ultimasNotas): ?>
            <tr><td colspan="5">Aún no hay notas registradas.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="megaphone"></svg> Últimos avisos</h2>
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

