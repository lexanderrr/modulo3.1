<?php $pagina = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar-scrim"></div>
<aside class="sidebar">
  <div class="marca">
    <div class="icono">PN</div>
    <div class="texto">Portal de Notas<span>Panel Administrador</span></div>
    <button type="button" class="sidebar-toggle" id="pnSidebarToggle" title="Colapsar barra lateral">
      <svg class="lucide" data-lucide="panel-left-close"></svg>
    </button>
  </div>
  <nav>
    <div class="grupo-titulo">General</div>
    <a href="dashboard.php" class="<?= $pagina === 'dashboard.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="layout-grid"></svg><span class="etiqueta">Inicio</span>
    </a>

    <div class="grupo-titulo">Personas</div>
    <a href="estudiantes.php" class="<?= $pagina === 'estudiantes.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="graduation-cap"></svg><span class="etiqueta">Estudiantes</span>
    </a>
    <a href="padres.php" class="<?= $pagina === 'padres.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="users"></svg><span class="etiqueta">Padres de Familia</span>
    </a>
    <a href="profesores.php" class="<?= $pagina === 'profesores.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="briefcase"></svg><span class="etiqueta">Profesores</span>
    </a>
    <a href="solicitudes.php" class="<?= $pagina === 'solicitudes.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="inbox"></svg><span class="etiqueta">Solicitudes</span>
    </a>

    <div class="grupo-titulo">Académico</div>
    <a href="materias.php" class="<?= $pagina === 'materias.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="book-open"></svg><span class="etiqueta">Materias</span>
    </a>
    <a href="notas.php" class="<?= $pagina === 'notas.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="star"></svg><span class="etiqueta">Notas</span>
    </a>
    <a href="asistencia.php" class="<?= $pagina === 'asistencia.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="calendar-check"></svg><span class="etiqueta">Asistencia</span>
    </a>

    <div class="grupo-titulo">Finanzas</div>
    <a href="pagos.php" class="<?= $pagina === 'pagos.php' || $pagina === 'recibo_pago.php' ? 'activo' : '' ?>">
      <svg class="lucide" data-lucide="credit-card"></svg><span class="etiqueta">Pagos</span>
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