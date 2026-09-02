<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

if ($_SESSION['admin_rol'] !== 'profesor') {
    header('Location: ../admin/dashboard.php');
    exit;
}

// Obtener materias del profesor
$stmt = $pdo->prepare('SELECT * FROM materias WHERE docente LIKE ? ORDER BY nombre');
$stmt->execute(['%' . $_SESSION['admin_nombre'] . '%']);
$misMaterias = $stmt->fetchAll();
$idsMaterias = array_column($misMaterias, 'id');

// Obtener estudiantes del profesor
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

    if (!$estudiantes) {
        $grados = array_unique(array_column($misMaterias, 'grado'));
        if ($grados) {
            $inG = implode(',', array_fill(0, count($grados), '?'));
            $stmt = $pdo->prepare("SELECT id, carnet, nombre, apellido, grado, seccion FROM estudiantes WHERE grado IN ($inG) ORDER BY apellido, nombre");
            $stmt->execute(array_values($grados));
            $estudiantes = $stmt->fetchAll();
        }
    }
}

// Guardar asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $asistencias = $_POST['asistencia'] ?? [];

    foreach ($asistencias as $idEst => $estado) {
        if (in_array($estado, ['Presente', 'Ausente', 'Tardanza'], true)) {
            $stmt = $pdo->prepare('
                INSERT INTO asistencia (id_estudiante, fecha, estado)
                VALUES (?,?,?)
                ON DUPLICATE KEY UPDATE estado = VALUES(estado)
            ');
            $stmt->execute([(int)$idEst, $fecha, $estado]);
        }
    }
    header('Location: asistencia.php?ok=1&fecha=' . $fecha);
    exit;
}

$fechaFiltro = $_GET['fecha'] ?? date('Y-m-d');

// Obtener asistencias de la fecha seleccionada
$asistenciasHoy = [];
if ($estudiantes) {
    $idsEst = array_column($estudiantes, 'id');
    $inE = implode(',', array_fill(0, count($idsEst), '?'));
    $stmt = $pdo->prepare("SELECT id_estudiante, estado FROM asistencia WHERE fecha = ? AND id_estudiante IN ($inE)");
    $stmt->execute(array_merge([$fechaFiltro], $idsEst));
    foreach ($stmt->fetchAll() as $row) {
        $asistenciasHoy[$row['id_estudiante']] = $row['estado'];
    }
}

// Estadísticas del mes
$mes = date('m', strtotime($fechaFiltro));
$anio = date('Y', strtotime($fechaFiltro));
$statsMensuales = [];
if ($estudiantes) {
    $idsEst = array_column($estudiantes, 'id');
    $inE = implode(',', array_fill(0, count($idsEst), '?'));
    $stmt = $pdo->prepare("
        SELECT id_estudiante, 
               COUNT(*) AS total,
               SUM(CASE WHEN estado='Presente' THEN 1 ELSE 0 END) AS presentes,
               SUM(CASE WHEN estado='Ausente' THEN 1 ELSE 0 END) AS ausentes,
               SUM(CASE WHEN estado='Tardanza' THEN 1 ELSE 0 END) AS tardanzas
        FROM asistencia 
        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND id_estudiante IN ($inE)
        GROUP BY id_estudiante
    ");
    $stmt->execute(array_merge([$mes, $anio], $idsEst));
    foreach ($stmt->fetchAll() as $row) {
        $statsMensuales[$row['id_estudiante']] = $row;
    }
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
  <?php include __DIR__ . '/../includes/sidebar_profesor.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Control de Asistencia Diaria'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Asistencia guardada correctamente.</div>
    <?php endif; ?>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="calendar-check"></svg> Asistencia diaria</h2>
      <form method="GET" style="margin-bottom:18px;display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
        <div class="form-grupo" style="margin:0;">
          <label>Seleccionar fecha</label>
          <input type="date" name="fecha" value="<?= h($fechaFiltro) ?>" onchange="this.form.submit()" style="width:auto;">
        </div>
        <span style="color:var(--gris-texto);font-size:13px;">
          <svg class="lucide" data-lucide="info"></svg> 
          <?= h(mb_strtoupper(mesAnioEs(strtotime($fechaFiltro)))) ?>
        </span>
      </form>

      <form method="POST">
        <input type="hidden" name="fecha" value="<?= h($fechaFiltro) ?>">
        <div class="tabla-wrap">
          <table class="tabla-datos">
            <thead>
              <tr>
                <th>#</th>
                <th>Estudiante</th>
                <th>Grado</th>
                <th>Presente</th>
                <th>Ausente</th>
                <th>Tardanza</th>
              </tr>
            </thead>
            <tbody>
            <?php $idx = 1; foreach ($estudiantes as $e): ?>
              <tr>
                <td><?= $idx++ ?></td>
                <td><?= h($e['nombre'] . ' ' . $e['apellido']) ?> <small style="color:var(--gris-texto);">(<?= h($e['carnet']) ?>)</small></td>
                <td><?= h($e['grado']) ?></td>
                <td>
                  <input type="radio" name="asistencia[<?= $e['id'] ?>]" value="Presente" 
                    <?= (!isset($asistenciasHoy[$e['id']]) || $asistenciasHoy[$e['id']] === 'Presente') ? 'checked' : '' ?>
                    style="accent-color:var(--verde);width:18px;height:18px;cursor:pointer;">
                </td>
                <td>
                  <input type="radio" name="asistencia[<?= $e['id'] ?>]" value="Ausente"
                    <?= (isset($asistenciasHoy[$e['id']]) && $asistenciasHoy[$e['id']] === 'Ausente') ? 'checked' : '' ?>
                    style="accent-color:var(--rojo);width:18px;height:18px;cursor:pointer;">
                </td>
                <td>
                  <input type="radio" name="asistencia[<?= $e['id'] ?>]" value="Tardanza"
                    <?= (isset($asistenciasHoy[$e['id']]) && $asistenciasHoy[$e['id']] === 'Tardanza') ? 'checked' : '' ?>
                    style="accent-color:var(--dorado);width:18px;height:18px;cursor:pointer;">
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$estudiantes): ?>
              <tr><td colspan="6">No hay estudiantes registrados en tus materias.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($estudiantes): ?>
          <button type="submit" class="btn-primario" style="width:auto;padding:11px 28px;margin-top:16px;">
            <svg class="lucide" data-lucide="save"></svg> Guardar asistencia del día
          </button>
        <?php endif; ?>
      </form>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="bar-chart-3"></svg> Resumen mensual — <?= h(mesAnioEs(strtotime($fechaFiltro))) ?></h2>
      <div class="tabla-wrap">
        <table class="tabla-datos">
          <thead><tr><th>Estudiante</th><th>Total</th><th>Presentes</th><th>Ausentes</th><th>Tardanzas</th><th>% Asistencia</th></tr></thead>
          <tbody>
          <?php foreach ($estudiantes as $e): 
            $stats = $statsMensuales[$e['id']] ?? null;
            $total = $stats ? (int)$stats['total'] : 0;
            $presentes = $stats ? (int)$stats['presentes'] : 0;
            $ausentes = $stats ? (int)$stats['ausentes'] : 0;
            $tardanzas = $stats ? (int)$stats['tardanzas'] : 0;
            $porcentaje = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;
          ?>
            <tr>
              <td><?= h($e['nombre'] . ' ' . $e['apellido']) ?></td>
              <td><?= $total ?></td>
              <td style="color:var(--verde);font-weight:600;"><?= $presentes ?></td>
              <td style="color:var(--rojo);font-weight:600;"><?= $ausentes ?></td>
              <td style="color:var(--dorado);font-weight:600;"><?= $tardanzas ?></td>
              <td>
                <span class="badge-nota <?= $porcentaje >= 80 ? 'aprobado' : 'reprobado' ?>"><?= $porcentaje ?>%</span>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$estudiantes): ?>
            <tr><td colspan="6">No hay datos disponibles.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
