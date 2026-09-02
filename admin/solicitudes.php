<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirAdmin();

// Solo administradores reales pueden acceder a este módulo (no profesores ni cajeros)
if (($_SESSION['admin_rol'] ?? '') !== 'admin') {
    header('Location: ../profesor/dashboard.php');
    exit;
}

$mensaje = '';
$credencialGenerada = null; // ['usuario' => ..., 'password' => ...] para mostrar una sola vez

function generarPasswordTemporal(): string {
    return substr(bin2hex(random_bytes(5)), 0, 8) . 'A1!';
}

function generarUsuarioDisponible(PDO $pdo, string $base): string {
    $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $base)) ?: 'padre';
    $usuario = $base;
    $i = 1;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM padres WHERE usuario = ?');
    while (true) {
        $stmt->execute([$usuario]);
        if ((int)$stmt->fetchColumn() === 0) return $usuario;
        $i++;
        $usuario = $base . $i;
    }
}

// --- Aprobar / rechazar solicitud de cuenta ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_cuenta'])) {
    $id = (int)($_POST['id'] ?? 0);
    $accion = $_POST['accion_cuenta'];

    $stmt = $pdo->prepare("SELECT * FROM solicitudes_cuenta WHERE id = ? AND estado = 'pendiente'");
    $stmt->execute([$id]);
    $sol = $stmt->fetch();

    if ($sol) {
        if ($accion === 'rechazar') {
            $pdo->prepare("UPDATE solicitudes_cuenta SET estado = 'rechazada' WHERE id = ?")->execute([$id]);
            $mensaje = 'Solicitud de cuenta rechazada.';
        } elseif ($accion === 'aprobar') {
            $partes = explode(' ', trim($sol['nombre_completo']), 2);
            $nombre = $partes[0];
            $apellido = $partes[1] ?? '';
            $usuario = generarUsuarioDisponible($pdo, explode('@', $sol['correo'])[0]);
            $passwordTemp = generarPasswordTemporal();
            $hash = password_hash($passwordTemp, PASSWORD_DEFAULT);

            $pdo->beginTransaction();
            $stmtPadre = $pdo->prepare('INSERT INTO padres (nombre, apellido, correo, telefono, usuario, password) VALUES (?,?,?,?,?,?)');
            $stmtPadre->execute([$nombre, $apellido, $sol['correo'], $sol['telefono'], $usuario, $hash]);
            $idPadre = (int)$pdo->lastInsertId();

            // Si la solicitud venía vinculada a un estudiante existente, se asigna como responsable
            if (!empty($sol['id_estudiante'])) {
                $pdo->prepare('UPDATE estudiantes SET id_padre = ? WHERE id = ?')->execute([$idPadre, $sol['id_estudiante']]);
            }

            $pdo->prepare("UPDATE solicitudes_cuenta SET estado = 'aprobada' WHERE id = ?")->execute([$id]);
            $pdo->commit();

            $credencialGenerada = ['usuario' => $usuario, 'password' => $passwordTemp, 'tipo' => 'cuenta de padre'];
            $mensaje = 'Cuenta de padre creada correctamente.';
        }
    } else {
        $mensaje = 'La solicitud ya fue procesada o no existe.';
    }
}

// --- Aprobar / rechazar solicitud de recuperación de contraseña ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_password'])) {
    $id = (int)($_POST['id'] ?? 0);
    $accion = $_POST['accion_password'];

    $stmt = $pdo->prepare("SELECT * FROM solicitudes_password WHERE id = ? AND estado = 'pendiente'");
    $stmt->execute([$id]);
    $sol = $stmt->fetch();

    if ($sol) {
        if ($accion === 'rechazar') {
            $pdo->prepare("UPDATE solicitudes_password SET estado = 'rechazada' WHERE id = ?")->execute([$id]);
            $mensaje = 'Solicitud de contraseña rechazada.';
        } elseif ($accion === 'aprobar') {
            $passwordTemp = generarPasswordTemporal();
            $hash = password_hash($passwordTemp, PASSWORD_DEFAULT);
            $tabla = $sol['tipo_usuario'] === 'padre' ? 'padres' : 'administradores';

            $pdo->prepare("UPDATE {$tabla} SET password = ? WHERE id = ?")->execute([$hash, $sol['id_usuario']]);
            $pdo->prepare("UPDATE solicitudes_password SET estado = 'aprobada' WHERE id = ?")->execute([$id]);

            $stmtU = $pdo->prepare("SELECT usuario FROM {$tabla} WHERE id = ?");
            $stmtU->execute([$sol['id_usuario']]);
            $usuarioAfectado = $stmtU->fetchColumn();

            $credencialGenerada = ['usuario' => $usuarioAfectado, 'password' => $passwordTemp, 'tipo' => 'restablecimiento de contraseña'];
            $mensaje = 'Contraseña restablecida correctamente.';
        }
    } else {
        $mensaje = 'La solicitud ya fue procesada o no existe.';
    }
}

$solicitudesCuenta = $pdo->query("SELECT * FROM solicitudes_cuenta WHERE estado = 'pendiente' ORDER BY creado_en DESC")->fetchAll();
$solicitudesPassword = $pdo->query("SELECT * FROM solicitudes_password WHERE estado = 'pendiente' ORDER BY creado_en DESC")->fetchAll();
$historial = $pdo->query("
    (SELECT 'cuenta' AS tipo, id, nombre_completo AS detalle, correo, estado, creado_en FROM solicitudes_cuenta WHERE estado != 'pendiente')
    UNION ALL
    (SELECT 'password' AS tipo, id, tipo_usuario AS detalle, correo, estado, creado_en FROM solicitudes_password WHERE estado != 'pendiente')
    ORDER BY creado_en DESC LIMIT 20
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Solicitudes | Portal de Notas</title>
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
    <?php $tituloPagina = 'Solicitudes'; include __DIR__ . '/../includes/topbar.php'; ?>

    <?php if ($mensaje): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> <?= h($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($credencialGenerada): ?>
      <div class="alerta alerta-ok" style="line-height:1.7;">
        <svg class="lucide" data-lucide="key-round"></svg>
        Credenciales generadas (<?= h($credencialGenerada['tipo']) ?>) — <strong>guárdalas ahora, no volverán a mostrarse</strong>:<br>
        Usuario: <code><?= h($credencialGenerada['usuario']) ?></code> &nbsp;·&nbsp;
        Contraseña temporal: <code><?= h($credencialGenerada['password']) ?></code>
      </div>
    <?php endif; ?>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="user-plus"></svg> Solicitudes de cuenta de padre (<?= count($solicitudesCuenta) ?> pendientes)</h2>
      <table class="tabla-datos">
        <thead><tr><th>Solicitante</th><th>Correo</th><th>Teléfono</th><th>Estudiante</th><th>Fecha</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($solicitudesCuenta as $s): ?>
          <tr>
            <td><?= h($s['nombre_completo']) ?></td>
            <td><?= h($s['correo']) ?></td>
            <td><?= h($s['telefono'] ?: '—') ?></td>
            <td><?= h($s['nombre_estudiante']) ?><?= $s['id_estudiante'] ? ' <span title="Vinculado a un estudiante existente">✓</span>' : '' ?></td>
            <td><?= h(date('d/m/Y', strtotime($s['creado_en']))) ?></td>
            <td>
              <form method="POST" style="display:inline;" onsubmit="return confirm('¿Aprobar esta solicitud y crear la cuenta?');">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" name="accion_cuenta" value="aprobar" class="btn-sm"><svg class="lucide" data-lucide="check"></svg></button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" name="accion_cuenta" value="rechazar" class="btn-sm btn-eliminar"><svg class="lucide" data-lucide="x"></svg></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$solicitudesCuenta): ?>
          <tr><td colspan="6">No hay solicitudes de cuenta pendientes.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="key-round"></svg> Solicitudes de recuperación de contraseña (<?= count($solicitudesPassword) ?> pendientes)</h2>
      <table class="tabla-datos">
        <thead><tr><th>Tipo</th><th>Correo</th><th>Fecha</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($solicitudesPassword as $s): ?>
          <tr>
            <td><?= $s['tipo_usuario'] === 'padre' ? 'Padre de familia' : 'Profesor / Administrador' ?></td>
            <td><?= h($s['correo']) ?></td>
            <td><?= h(date('d/m/Y', strtotime($s['creado_en']))) ?></td>
            <td>
              <form method="POST" style="display:inline;" onsubmit="return confirm('¿Restablecer la contraseña de esta cuenta?');">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" name="accion_password" value="aprobar" class="btn-sm"><svg class="lucide" data-lucide="check"></svg></button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" name="accion_password" value="rechazar" class="btn-sm btn-eliminar"><svg class="lucide" data-lucide="x"></svg></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$solicitudesPassword): ?>
          <tr><td colspan="4">No hay solicitudes de contraseña pendientes.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="history"></svg> Historial reciente</h2>
      <table class="tabla-datos">
        <thead><tr><th>Tipo</th><th>Detalle</th><th>Correo</th><th>Estado</th><th>Fecha</th></tr></thead>
        <tbody>
        <?php foreach ($historial as $h): ?>
          <tr>
            <td><?= $h['tipo'] === 'cuenta' ? 'Cuenta de padre' : 'Contraseña' ?></td>
            <td><?= h($h['detalle']) ?></td>
            <td><?= h($h['correo']) ?></td>
            <td><?= h(ucfirst($h['estado'])) ?></td>
            <td><?= h(date('d/m/Y', strtotime($h['creado_en']))) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$historial): ?>
          <tr><td colspan="5">Aún no hay solicitudes procesadas.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
