<?php
// Se espera que la página que incluye este archivo defina $tituloPagina antes del include.
// Detecta el rol activo a partir de la sesión ya existente (sin alterar la lógica de sesion.php).
$esAdmin = !empty($_SESSION['admin_id']);
$nombreUsuario = $esAdmin ? ($_SESSION['admin_nombre'] ?? 'Administrador') : ($_SESSION['padre_nombre'] ?? 'Padre de familia');
$rolEtiqueta = $esAdmin ? 'Administrador' : 'Padre de Familia';
$iniciales = '';
foreach (explode(' ', trim($nombreUsuario)) as $parte) {
    if ($parte !== '') { $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1)); }
    if (mb_strlen($iniciales) >= 2) break;
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

    <div class="centro-notif">
      <button type="button" class="icon-btn" data-menu-toggle title="Notificaciones">
        <svg class="lucide" data-lucide="bell"></svg>
        <span class="punto-notif"></span>
      </button>
      <div class="dropdown">
        <div class="dd-header">Notificaciones</div>
        <div class="dd-vacio">No tienes notificaciones nuevas.</div>
      </div>
    </div>

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
        <div class="dd-header"><?= h($nombreUsuario) ?></div>
        <a href="<?= $esAdmin ? '../logout.php' : '../logout.php' ?>" class="dd-item rojo">
          <svg class="lucide" data-lucide="log-out"></svg> Cerrar sesión
        </a>
      </div>
    </div>
  </div>
</div>
