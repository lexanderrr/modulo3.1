<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

if ($_SESSION['admin_rol'] !== 'profesor') {
    header('Location: ../admin/dashboard.php');
    exit;
}

// Obtener las materias del profesor
$stmt = $pdo->prepare('SELECT * FROM materias WHERE docente LIKE ? ORDER BY nombre');
$stmt->execute(['%' . $_SESSION['admin_nombre'] . '%']);
$misMaterias = $stmt->fetchAll();
$idsMaterias = array_column($misMaterias, 'id');

$estudiantes = [];
if ($idsMaterias) {
    $in = implode(',', array_fill(0, count($idsMaterias), '?'));
    $stmt = $pdo->prepare("
        SELECT DISTINCT e.id, e.carnet, e.nombre, e.apellido, e.grado, e.seccion
        FROM estudiantes e
        JOIN notas n ON n.id_estudiante = e.id
        WHERE n.id_materia IN ($in)
        ORDER BY e.apellido, e.nombre
    ");
    $stmt->execute($idsMaterias);
    $estudiantes = $stmt->fetchAll();
}

// Si no hay estudiantes via notas, mostrar todos los que coincidan con el grado de las materias
if (!$estudiantes && $misMaterias) {
    $grados = array_unique(array_column($misMaterias, 'grado'));
    $inG = implode(',', array_fill(0, count($grados), '?'));
    $stmt = $pdo->prepare("SELECT id, carnet, nombre, apellido, grado, seccion FROM estudiantes WHERE grado IN ($inG) ORDER BY apellido, nombre");
    $stmt->execute(array_values($grados));
    $estudiantes = $stmt->fetchAll();
}

$filtroGrado = $_GET['grado'] ?? '';
if ($filtroGrado && $estudiantes) {
    $estudiantes = array_filter($estudiantes, function($e) use ($filtroGrado) {
        return $e['grado'] === $filtroGrado;
    });
}

$grados = array_unique(array_column($estudiantes, 'grado'));
sort($grados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Alumnos | Portal de Notas</title>
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
    <?php $tituloPagina = 'Listado de Alumnos'; include __DIR__ . '/../includes/topbar.php'; ?>

    <div class="panel">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
        <h2 style="margin:0;"><svg class="lucide" data-lucide="graduation-cap"></svg> Mis alumnos</h2>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
          <select name="grado" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--borde);border-radius:10px;background:var(--tarjeta-solida);color:var(--texto);">
            <option value="">Todos los grados</option>
            <?php foreach ($grados as $g): ?>
              <option value="<?= h($g) ?>" <?= $filtroGrado === $g ? 'selected' : '' ?>><?= h($g) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>

      <?php if (!$misMaterias): ?>
        <div class="alerta alerta-error"><svg class="lucide" data-lucide="alert-circle"></svg> No tienes materias asignadas. Contacta a la secretaría para que te asigne materias.</div>
      <?php endif; ?>

      <div class="tabla-wrap">
        <table class="tabla-datos">
          <thead><tr><th>Carnet</th><th>Nombre completo</th><th>Grado</th><th>Sección</th></tr></thead>
          <tbody>
          <?php foreach ($estudiantes as $e): ?>
            <tr>
              <td><?= h($e['carnet']) ?></td>
              <td><?= h($e['nombre'] . ' ' . $e['apellido']) ?></td>
              <td><?= h($e['grado']) ?></td>
              <td><?= h($e['seccion']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$estudiantes): ?>
            <tr><td colspan="4">No hay estudiantes vinculados a tus materias.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
      <p style="color:var(--gris-texto);font-size:13px;margin-top:12px;">Total: <?= count($estudiantes) ?> alumno(s)</p>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
