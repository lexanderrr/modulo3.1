<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

// Solo administradores reales pueden acceder a este módulo (no profesores ni cajeros)
if (($_SESSION['admin_rol'] ?? '') !== 'admin') {
    header('Location: ../profesor/dashboard.php');
    exit;
}

if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare('DELETE FROM avisos WHERE id = ?');
    $stmt->execute([(int)$_GET['eliminar']]);
    header('Location: avisos.php?ok=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    if ($titulo && $contenido) {
        $stmt = $pdo->prepare('INSERT INTO avisos (titulo, contenido, id_admin) VALUES (?,?,?)');
        $stmt->execute([$titulo, $contenido, $_SESSION['admin_id']]);
        header('Location: avisos.php?ok=1');
        exit;
    }
}

$avisos = $pdo->query('
    SELECT a.*, ad.nombre AS admin_nombre
    FROM avisos a LEFT JOIN administradores ad ON ad.id = a.id_admin
    ORDER BY a.fecha DESC
')->fetchAll();
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
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Avisos y Comunicados'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Cambios guardados correctamente.</div>
    <?php endif; ?>

    <div class="panel">
      <h2>Publicar nuevo aviso</h2>
      <form method="POST">
        <div class="form-grupo"><label>Título</label><input type="text" name="titulo" required></div>
        <div class="form-grupo"><label>Contenido</label>
          <textarea name="contenido" rows="4" required style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;font-family:inherit;"></textarea>
        </div>
        <button type="submit" class="btn-primario" style="width:auto;padding:11px 26px;"><svg class="lucide" data-lucide="megaphone"></svg> Publicar aviso</button>
      </form>
    </div>

    <div class="panel">
      <h2>Avisos publicados</h2>
      <?php foreach ($avisos as $a): ?>
        <div class="aviso-card">
          <div class="fecha"><?= h(date('d/m/Y H:i', strtotime($a['fecha']))) ?> · <?= h($a['admin_nombre']) ?></div>
          <h3><?= h($a['titulo']) ?></h3>
          <p style="margin:0 0 10px;color:var(--gris-texto);"><?= nl2br(h($a['contenido'])) ?></p>
          <a class="btn-sm btn-eliminar" href="avisos.php?eliminar=<?= $a['id'] ?>" onclick="return confirm('¿Eliminar este aviso?');"><svg class="lucide" data-lucide="trash-2"></svg> Eliminar</a>
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
