<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

$mensaje = '';

if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare('DELETE FROM padres WHERE id = ?');
    $stmt->execute([(int)$_GET['eliminar']]);
    header('Location: padres.php?ok=eliminado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo   = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $usuario  = trim($_POST['usuario']);
    $password = $_POST['password'] ?? '';

    if ($nombre && $apellido && $usuario) {
        if ($id) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE padres SET nombre=?, apellido=?, correo=?, telefono=?, usuario=?, password=? WHERE id=?');
                $stmt->execute([$nombre, $apellido, $correo, $telefono, $usuario, $hash, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE padres SET nombre=?, apellido=?, correo=?, telefono=?, usuario=? WHERE id=?');
                $stmt->execute([$nombre, $apellido, $correo, $telefono, $usuario, $id]);
            }
        } else {
            if ($password === '') {
                $mensaje = 'Debes asignar una contraseña para el nuevo usuario.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO padres (nombre, apellido, correo, telefono, usuario, password) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$nombre, $apellido, $correo, $telefono, $usuario, $hash]);
            }
        }
        if (!$mensaje) { header('Location: padres.php?ok=guardado'); exit; }
    } else {
        $mensaje = 'Completa nombre, apellido y usuario.';
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT * FROM padres WHERE id = ?');
    $stmt->execute([(int)$_GET['editar']]);
    $editar = $stmt->fetch();
}

$padres = $pdo->query('SELECT * FROM padres ORDER BY apellido, nombre')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Padres de Familia | Portal de Notas</title>
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
    <?php $tituloPagina = 'Cuentas de Padres de Familia'; include __DIR__ . '/../includes/topbar.php'; ?>
    <?php if (isset($_GET['ok'])): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Cambios guardados correctamente.</div>
    <?php endif; ?>
    <?php if ($mensaje): ?>
      <div class="alerta alerta-error"><svg class="lucide" data-lucide="alert-circle"></svg> <?= h($mensaje) ?></div>
    <?php endif; ?>

    <div class="panel">
      <h2><?= $editar ? 'Editar cuenta de padre/madre' : 'Registrar nuevo padre/madre' ?></h2>
      <form method="POST">
        <input type="hidden" name="id" value="<?= h($editar['id'] ?? '') ?>">
        <div class="form-inline-grid">
          <div class="form-grupo"><label>Nombre</label><input type="text" name="nombre" value="<?= h($editar['nombre'] ?? '') ?>" required></div>
          <div class="form-grupo"><label>Apellido</label><input type="text" name="apellido" value="<?= h($editar['apellido'] ?? '') ?>" required></div>
          <div class="form-grupo"><label>Correo</label><input type="email" name="correo" value="<?= h($editar['correo'] ?? '') ?>"></div>
          <div class="form-grupo"><label>Teléfono</label><input type="text" name="telefono" value="<?= h($editar['telefono'] ?? '') ?>"></div>
          <div class="form-grupo"><label>Usuario</label><input type="text" name="usuario" value="<?= h($editar['usuario'] ?? '') ?>" required></div>
          <div class="form-grupo"><label>Contraseña <?= $editar ? '(dejar en blanco para no cambiar)' : '' ?></label><input type="password" name="password"></div>
          <div class="form-grupo"><button type="submit" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> Guardar</button></div>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2>Listado de padres</h2>
      <table class="tabla-datos">
        <thead><tr><th>Nombre</th><th>Usuario</th><th>Correo</th><th>Teléfono</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($padres as $p): ?>
          <tr>
            <td><?= h($p['nombre'] . ' ' . $p['apellido']) ?></td>
            <td><?= h($p['usuario']) ?></td>
            <td><?= h($p['correo']) ?></td>
            <td><?= h($p['telefono']) ?></td>
            <td>
              <a class="btn-sm btn-editar" href="padres.php?editar=<?= $p['id'] ?>"><svg class="lucide" data-lucide="pencil"></svg></a>
              <a class="btn-sm btn-eliminar" href="padres.php?eliminar=<?= $p['id'] ?>" onclick="return confirm('¿Eliminar este padre/madre y sus estudiantes asociados?');"><svg class="lucide" data-lucide="trash-2"></svg></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$padres): ?>
          <tr><td colspan="5">No hay padres registrados.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
