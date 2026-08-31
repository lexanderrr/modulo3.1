<?php
// Se espera que la página que incluye este archivo defina $tituloPagina antes del include.
require_once __DIR__ . '/sesion.php';
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/conexion.php';
}

$esAdmin = !empty($_SESSION['admin_id']);
$rolActual = $esAdmin ? ($_SESSION['admin_rol'] ?? 'admin') : 'padre';
$nombreUsuario = $esAdmin ? ($_SESSION['admin_nombre'] ?? 'Administrador') : ($_SESSION['padre_nombre'] ?? 'Padre de familia');

if ($rolActual === 'profesor') {
    $rolEtiqueta = 'Profesor';
} elseif ($rolActual === 'cajero') {
    $rolEtiqueta = 'Cajero';
} elseif ($esAdmin) {
    $rolEtiqueta = 'Administrador';
} else {
    $rolEtiqueta = 'Padre de Familia';
}

$iniciales = '';
foreach (explode(' ', trim($nombreUsuario)) as $parte) {
    if ($parte !== '') { $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1)); }
    if (mb_strlen($iniciales) >= 2) break;
}

// Carga de notificaciones dinámicas según el rol
$notificaciones = [];

try {
    if (!$esAdmin && !empty($_SESSION['padre_id'])) {
        $idPadre = (int)$_SESSION['padre_id'];

        // 1. Avisos generales
        $stmt = $pdo->query('SELECT id, titulo, contenido, fecha FROM avisos ORDER BY fecha DESC LIMIT 10');
        while ($av = $stmt->fetch()) {
            $notificaciones[] = [
                'tipo'    => 'aviso',
                'icono'   => 'megaphone',
                'color'   => 'bg-azul',
                'titulo'  => 'Aviso: ' . $av['titulo'],
                'desc'    => $av['contenido'],
                'enlace'  => 'avisos.php',
                'fecha'   => $av['fecha'],
            ];
        }

        // 2. Calificaciones registradas de sus hijos
        $stmt = $pdo->prepare('
            SELECT n.nota, n.fecha_registro, e.nombre AS est_nom, m.nombre AS materia
            FROM notas n
            JOIN estudiantes e ON e.id = n.id_estudiante
            JOIN materias m ON m.id = n.id_materia
            WHERE e.id_padre = ?
            ORDER BY n.fecha_registro DESC LIMIT 15
        ');
        $stmt->execute([$idPadre]);
        while ($nt = $stmt->fetch()) {
            $notificaciones[] = [
                'tipo'    => 'nota',
                'icono'   => 'star',
                'color'   => 'bg-dorado',
                'titulo'  => 'Nota de ' . $nt['est_nom'],
                'desc'    => $nt['materia'] . ': ' . number_format((float)$nt['nota'], 2),
                'enlace'  => 'notas.php',
                'fecha'   => $nt['fecha_registro'],
            ];
        }

        // 3. Alertas de asistencia (ausencias o tardanzas)
        $stmt = $pdo->prepare('
            SELECT a.fecha, a.estado, a.observacion, e.nombre AS est_nom
            FROM asistencia a
            JOIN estudiantes e ON e.id = a.id_estudiante
            WHERE e.id_padre = ? AND a.estado IN ("Ausente", "Tardanza")
            ORDER BY a.fecha DESC LIMIT 10
        ');
        $stmt->execute([$idPadre]);
        while ($as = $stmt->fetch()) {
            $notificaciones[] = [
                'tipo'    => 'asistencia',
                'icono'   => $as['estado'] === 'Ausente' ? 'user-x' : 'clock',
                'color'   => $as['estado'] === 'Ausente' ? 'bg-rojo' : 'bg-dorado',
                'titulo'  => $as['estado'] . ': ' . $as['est_nom'],
                'desc'    => 'Fecha: ' . $as['fecha'] . ($as['observacion'] ? ' · ' . $as['observacion'] : ''),
                'enlace'  => 'asistencia.php',
                'fecha'   => $as['fecha'],
            ];
        }

    } elseif ($rolActual === 'profesor') {
        // 1. Avisos generales
        $stmt = $pdo->query('SELECT id, titulo, contenido, fecha FROM avisos ORDER BY fecha DESC LIMIT 10');
        while ($av = $stmt->fetch()) {
            $notificaciones[] = [
                'tipo'    => 'aviso',
                'icono'   => 'megaphone',
                'color'   => 'bg-azul',
                'titulo'  => 'Aviso: ' . $av['titulo'],
                'desc'    => $av['contenido'],
                'enlace'  => 'avisos.php',
                'fecha'   => $av['fecha'],
            ];
        }

        // 2. Reportes de conducta recientes
        $stmt = $pdo->query('
            SELECT c.fecha, c.tipo, c.descripcion, e.nombre AS est_nom, e.apellido AS est_ape
            FROM conducta c
            JOIN estudiantes e ON e.id = c.id_estudiante
            ORDER BY c.fecha DESC LIMIT 10
        ');
        while ($c = $stmt->fetch()) {
            $notificaciones[] = [
                'tipo'    => 'conducta',
                'icono'   => 'clipboard-list',
                'color'   => $c['tipo'] === 'Grave' ? 'bg-rojo' : ($c['tipo'] === 'Leve' ? 'bg-dorado' : 'bg-verde'),
                'titulo'  => 'Conducta ' . $c['tipo'] . ': ' . $c['est_nom'] . ' ' . $c['est_ape'],
                'desc'    => $c['descripcion'],
                'enlace'  => 'conducta.php',
                'fecha'   => $c['fecha'],
            ];
        }

    } else {
        // Administrador / Cajero
        // 1. Avisos generales
        $stmt = $pdo->query('SELECT id, titulo, contenido, fecha FROM avisos ORDER BY fecha DESC LIMIT 10');
        while ($av = $stmt->fetch()) {
            $notificaciones[] = [
                'tipo'    => 'aviso',
                'icono'   => 'megaphone',
                'color'   => 'bg-azul',
                'titulo'  => 'Aviso: ' . $av['titulo'],
                'desc'    => $av['contenido'],
                'enlace'  => 'avisos.php',
                'fecha'   => $av['fecha'],
            ];
        }

        // 2. Calificaciones registradas
        $stmt = $pdo->query('
            SELECT n.nota, n.fecha_registro, e.nombre AS est_nom, e.apellido AS est_ape, m.nombre AS materia
            FROM notas n
            JOIN estudiantes e ON e.id = n.id_estudiante
            JOIN materias m ON m.id = n.id_materia
            ORDER BY n.fecha_registro DESC LIMIT 15
        ');
        while ($nt = $stmt->fetch()) {
            $notificaciones[] = [
                'tipo'    => 'nota',
                'icono'   => 'star',
                'color'   => 'bg-dorado',
                'titulo'  => 'Nueva nota: ' . $nt['est_nom'] . ' ' . $nt['est_ape'],
                'desc'    => $nt['materia'] . ': ' . number_format((float)$nt['nota'], 2),
                'enlace'  => 'notas.php',
                'fecha'   => $nt['fecha_registro'],
            ];
        }

        // 3. Ausencias y tardanzas recientes
        $stmt = $pdo->query('
            SELECT a.fecha, a.estado, a.observacion, e.nombre AS est_nom, e.apellido AS est_ape
            FROM asistencia a
            JOIN estudiantes e ON e.id = a.id_estudiante
            WHERE a.estado IN ("Ausente", "Tardanza")
            ORDER BY a.fecha DESC LIMIT 10
        ');
        while ($as = $stmt->fetch()) {
            $notificaciones[] = [
                'tipo'    => 'asistencia',
                'icono'   => $as['estado'] === 'Ausente' ? 'user-x' : 'clock',
                'color'   => $as['estado'] === 'Ausente' ? 'bg-rojo' : 'bg-dorado',
                'titulo'  => 'Asistencia: ' . $as['estado'] . ' (' . $as['est_nom'] . ')',
                'desc'    => 'Fecha: ' . $as['fecha'] . ($as['observacion'] ? ' · ' . $as['observacion'] : ''),
                'enlace'  => 'asistencia.php',
                'fecha'   => $as['fecha'],
            ];
        }
    }
} catch (Exception $e) {
    $notificaciones = [];
}

// Ordenar notificaciones por fecha descendente (lo más nuevo arriba, lo más antiguo abajo al hacer scroll)
usort($notificaciones, function($a, $b) {
    return strtotime($b['fecha'] ?? '') - strtotime($a['fecha'] ?? '');
});

$totalNotificaciones = count($notificaciones);

function formatoFechaRelativaNotif($fechaStr): string {
    if (!$fechaStr) return '';
    $timestamp = strtotime($fechaStr);
    if (!$timestamp) return (string)$fechaStr;
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Hace un momento';
    if ($diff < 3600) return 'Hace ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'Hace ' . floor($diff / 3600) . ' h';
    if ($diff < 172800) return 'Ayer';
    return date('d/m/Y', $timestamp);
}
?>
<div class="topbar">
  <button type="button" class="icon-btn" id="pnSidebarToggleMovil" title="Abrir menú">
    <svg class="lucide" data-lucide="menu"></svg>
  </button>

  <div class="titulo-pagina">
    <h1><?= h($tituloPagina ?? 'Portal de Notas') ?></h1>
    <div class="fecha-hora">
      <span id="pnFechaActual"></span>
      <span>·</span>
      <span id="pnHoraActual"></span>
    </div>
  </div>

  <div class="buscador">
    <svg class="lucide" data-lucide="search"></svg>
    <input type="text" placeholder="Buscar…" aria-label="Buscar en el portal">
  </div>

  <div class="acciones">
    <button type="button" class="theme-switch" id="pnThemeSwitch" title="Cambiar tema" aria-label="Alternar modo claro u oscuro">
      <span class="knob"><svg class="lucide" data-lucide="sun"></svg></span>
    </button>

    <!-- Centro de Notificaciones Dinámico -->
    <div class="centro-notif">
      <button type="button" class="icon-btn" data-menu-toggle title="Notificaciones (<?= $totalNotificaciones ?>)">
        <svg class="lucide" data-lucide="bell"></svg>
        <?php if ($totalNotificaciones > 0): ?>
          <span class="punto-notif-contador"><?= $totalNotificaciones > 99 ? '99+' : $totalNotificaciones ?></span>
        <?php endif; ?>
      </button>

      <div class="dropdown dd-notificaciones">
        <div class="dd-notif-header">
          <div class="dd-notif-header-titulo">
            <svg class="lucide" data-lucide="bell" style="width:16px;height:16px;color:var(--azul);"></svg>
            <span>Notificaciones</span>
          </div>
          <?php if ($totalNotificaciones > 0): ?>
            <span class="dd-notif-badge"><?= $totalNotificaciones ?> en total</span>
          <?php endif; ?>
        </div>

        <?php if ($totalNotificaciones > 0): ?>
          <div class="dd-notif-lista" id="pnListaNotificaciones">
            <?php foreach ($notificaciones as $n): ?>
              <a href="<?= h($n['enlace']) ?>" class="dd-notif-item">
                <div class="dd-notif-icono <?= h($n['color']) ?>">
                  <svg class="lucide" data-lucide="<?= h($n['icono']) ?>"></svg>
                </div>
                <div class="dd-notif-cuerpo">
                  <div class="dd-notif-titulo"><?= h($n['titulo']) ?></div>
                  <div class="dd-notif-desc"><?= h($n['desc']) ?></div>
                  <div class="dd-notif-tiempo"><?= h(formatoFechaRelativaNotif($n['fecha'])) ?></div>
                </div>
              </a>
            <?php endforeach; ?>
            <div class="dd-notif-fin">
              <svg class="lucide" data-lucide="check-check" style="width:14px;height:14px;color:var(--texto-terciario);"></svg>
              <span>Has llegado a la última notificación</span>
            </div>
          </div>
          <div class="dd-notif-footer">
            <a href="avisos.php">
              <svg class="lucide" data-lucide="megaphone" style="width:14px;height:14px;"></svg>
              Ir a todos los avisos y comunicados
            </a>
          </div>
        <?php else: ?>
          <div class="dd-vacio">
            <svg class="lucide" data-lucide="check-circle" style="width:28px;height:28px;color:var(--verde);margin-bottom:6px;"></svg>
            <div>No tienes notificaciones pendientes.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Menú Usuario -->
    <div class="menu-usuario">
      <div class="usuario-chip" data-menu-toggle>
        <div class="avatar"><?= h($iniciales ?: 'PN') ?></div>
        <div class="info">
          <span class="nombre"><?= h($nombreUsuario) ?></span>
          <span class="rol"><?= h($rolEtiqueta) ?></span>
        </div>
        <svg class="lucide chev" data-lucide="chevron-down"></svg>
      </div>
      <div class="dropdown">
        <div class="dd-header"><?= h($nombreUsuario) ?> (<?= h($rolEtiqueta) ?>)</div>
        <a href="../logout.php" class="dd-item rojo">
          <svg class="lucide" data-lucide="log-out"></svg> Cerrar sesión
        </a>
      </div>
    </div>
  </div>
</div>
