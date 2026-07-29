<?php $pagina = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar-scrim"></div>
<aside class="sidebar">
  <div class="marca">
    <div class="icono">PN</div>
    <div class="texto">Portal de Notas<span>Panel Profesor</span></div>
    <button type="button" class="sidebar-toggle" id="pnSidebarToggle" title="Colapsar barra lateral">
      <svg class="lucide" data-lucide="panel-left-close"></svg>
    </button>
  </div>
  <nav>
    <div class="grupo-titulo">General</div>
    <a href="dashboard.php" class="<?= $pagina === 'dashboard.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="layout-grid"></svg><span class="etiqueta">Inicio</span>
    </a>

    <div class="grupo-titulo">Académico</div>
    <a href="estudiantes.php" class="<?= $pagina === 'estudiantes.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="graduation-cap"></svg><span class="etiqueta">Estudiantes</span>
    </a>
    <a href="notas.php" class="<?= $pagina === 'notas.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="star"></svg><span class="etiqueta">Notas</span>
    </a>
    <a href="asistencia.php" class="<?= $pagina === 'asistencia.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="calendar-check"></svg><span class="etiqueta">Asistencia</span>
    </a>
    <a href="conducta.php" class="<?= $pagina === 'conducta.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="clipboard-list"></svg><span class="etiqueta">Conducta</span>
    </a>

    <div class="grupo-titulo">Comunicación</div>
    <a href="avisos.php" class="<?= $pagina === 'avisos.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="megaphone"></svg><span class="etiqueta">Avisos</span>
    </a>

    <div class="cerrar-sesion">
      <a href="../logout.php"><svg class="lucide" data-lucide="log-out"></svg><span class="etiqueta">Cerrar sesión</span></a>
    </div>
  </nav>
</aside>
