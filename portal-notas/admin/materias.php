<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare('DELETE FROM materias WHERE id = ?');
    $stmt->execute([(int)$_GET['eliminar']]);
    header('Location: materias.php?ok=eliminado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id'] ?? 0);
    $nombre  = trim($_POST['nombre']);
    $docente = trim($_POST['docente']);
    $grado   = trim($_POST['grado']);

    if ($nombre) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE materias SET nombre=?, docente=?, grado=? WHERE id=?');
            $stmt->execute([$nombre, $docente, $grado, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO materias (nombre, docente, grado) VALUES (?,?,?)');
            $stmt->execute([$nombre, $docente, $grado]);
        }
        header('Location: materias.php?ok=guardado');
        exit;
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT * FROM materias WHERE id = ?');
    $stmt->execute([(int)$_GET['editar']]);
    $editar = $stmt->fetch();
}

$materias = $pdo->query('SELECT * FROM materias ORDER BY nombre')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Materias | Portal de Notas</title>
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
    <?php $tituloPagina = 'Gestión de Materias'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Cambios guardados correctamente.</div>
    <?php endif; ?>

    <div class="panel">
      <h2><?= $editar ? 'Editar materia' : 'Registrar nueva materia' ?></h2>
      <form method="POST">
        <input type="hidden" name="id" value="<?= h($editar['id'] ?? '') ?>">
        <div class="form-inline-grid">
          <div class="form-grupo"><label>Nombre</label><input type="text" name="nombre" value="<?= h($editar['nombre'] ?? '') ?>" required></div>
          <div class="form-grupo"><label>Docente</label><input type="text" name="docente" value="<?= h($editar['docente'] ?? '') ?>"></div>
          <div class="form-grupo"><label>Grado</label><input type="text" name="grado" value="<?= h($editar['grado'] ?? '') ?>"></div>
          <div class="form-grupo"><button type="submit" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> Guardar</button></div>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2>Listado de materias</h2>
      <table class="tabla-datos">
        <thead><tr><th>Nombre</th><th>Docente</th><th>Grado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($materias as $m): ?>
          <tr>
            <td><?= h($m['nombre']) ?></td>
            <td><?= h($m['docente']) ?></td>
            <td><?= h($m['grado']) ?></td>
            <td>
              <a class="btn-sm btn-editar" href="materias.php?editar=<?= $m['id'] ?>"><svg class="lucide" data-lucide="pencil"></svg></a>
              <a class="btn-sm btn-eliminar" href="materias.php?eliminar=<?= $m['id'] ?>" onclick="return confirm('¿Eliminar esta materia?');"><svg class="lucide" data-lucide="trash-2"></svg></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$materias): ?>
          <tr><td colspan="4">No hay materias registradas.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
