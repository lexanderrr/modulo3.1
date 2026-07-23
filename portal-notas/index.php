<?php
require_once __DIR__ . '/includes/sesion.php';

// Si ya hay sesión activa, redirigir directo
if (!empty($_SESSION['admin_id'])) { header('Location: admin/dashboard.php'); exit; }
if (!empty($_SESSION['padre_id'])) { header('Location: padres/dashboard.php'); exit; }

$error = '';
if (isset($_GET['err'])) {
    $error = $_GET['err'] === 'sesion'
        ? 'Debes iniciar sesión para continuar.'
        : 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Portal de Notas | Instituto Nacional</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-side">
      <span class="badge-modulo">Módulo 3.1 · Desarrollo de Software</span>
      <h1><svg class="lucide" data-lucide="graduation-cap"></svg> Portal Web de Consulta de Notas</h1>
      <p>Consulta calificaciones, asistencia y avisos de tus hijos de forma rápida y segura, o gestiona la información académica del instituto desde el panel administrativo.</p>
    </div>
    <div class="login-form-side">
      <h2>Iniciar sesión</h2>
      <p class="subtitulo">Selecciona tu tipo de usuario para continuar</p>

      <?php if ($error): ?>
        <div class="alerta alerta-error"><svg class="lucide" data-lucide="alert-circle"></svg> <?= h($error) ?></div>
      <?php endif; ?>

      <div class="nav-tabs-rol">
        <button type="button" id="tabPadre" class="activo" onclick="mostrarTab('padre')"><svg class="lucide" data-lucide="user"></svg> Padre de Familia</button>
        <button type="button" id="tabAdmin" onclick="mostrarTab('admin')"><svg class="lucide" data-lucide="shield-check"></svg> Administrador</button>
      </div>

      <form id="formPadre" method="POST" action="login_procesar.php">
        <input type="hidden" name="rol" value="padre">
        <div class="form-grupo">
          <label>Usuario</label>
          <input type="text" name="usuario" placeholder="Ingresa tu usuario" required autofocus>
        </div>
        <div class="form-grupo">
          <label>Contraseña</label>
          <div class="campo-password">
            <input type="password" name="password" placeholder="••••••••" required>
            <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña"><svg class="lucide" data-lucide="eye"></svg></button>
          </div>
        </div>
        <label class="recordarme"><input type="checkbox" name="recordarme"> Recordarme en este dispositivo</label>
        <button type="submit" class="btn-primario"><svg class="lucide" data-lucide="log-in"></svg> Entrar como Padre</button>
      </form>

      <!-- ── Ayuda para padres (solo visible en tab padre) ── -->
      <div id="ayudaPadre" class="login-ayuda">
        <div class="login-ayuda-links">
          <button type="button" class="link-ayuda" id="btnSolicitarCuenta">
            <svg class="lucide" data-lucide="user-plus"></svg>
            ¿No tienes cuenta? <strong>Solicitarla aquí</strong>
          </button>
          <span class="separador-ayuda">·</span>
          <button type="button" class="link-ayuda" id="btnOlvidePassword">
            <svg class="lucide" data-lucide="key-round"></svg>
            ¿Olvidaste tu contraseña?
          </button>
        </div>
      </div>

      <form id="formAdmin" method="POST" action="login_procesar.php" style="display:none">
        <input type="hidden" name="rol" value="admin">
        <div class="form-grupo">
          <label>Usuario</label>
          <input type="text" name="usuario" placeholder="Ingresa tu usuario">
        </div>
        <div class="form-grupo">
          <label>Contraseña</label>
          <div class="campo-password">
            <input type="password" name="password" placeholder="••••••••">
            <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña"><svg class="lucide" data-lucide="eye"></svg></button>
          </div>
        </div>
        <label class="recordarme"><input type="checkbox" name="recordarme"> Recordarme en este dispositivo</label>
        <button type="submit" class="btn-primario"><svg class="lucide" data-lucide="log-in"></svg> Entrar como Administrador</button>
      </form>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════
       PANEL: Solicitar cuenta
  ════════════════════════════════════════════════ -->
  <div class="panel-modal-scrim" id="scrimSolicitar"></div>
  <div class="panel-modal" id="panelSolicitar" role="dialog" aria-modal="true" aria-labelledby="tituloSolicitar">
    <div class="panel-modal-header">
      <div class="panel-modal-icono bg-azul"><svg class="lucide" data-lucide="user-plus"></svg></div>
      <div>
        <h2 id="tituloSolicitar">Solicitar cuenta de padre</h2>
        <p>Completa el formulario y el administrador activará tu acceso.</p>
      </div>
      <button type="button" class="panel-modal-cerrar" id="cerrarSolicitar" aria-label="Cerrar">
        <svg class="lucide" data-lucide="x"></svg>
      </button>
    </div>
    <div class="panel-modal-body">
      <div class="form-grupo">
        <label>Nombre completo</label>
        <input type="text" id="solNombre" placeholder="Ej. María García" autocomplete="name">
      </div>
      <div class="form-grupo">
        <label>Correo electrónico</label>
        <input type="email" id="solCorreo" placeholder="correo@ejemplo.com" autocomplete="email">
      </div>
      <div class="form-grupo">
        <label>Teléfono (opcional)</label>
        <input type="text" id="solTelefono" placeholder="7000-0000" autocomplete="tel">
      </div>
      <div class="form-grupo">
        <label>Nombre(s) del estudiante vinculado</label>
        <input type="text" id="solEstudiante" placeholder="Ej. Ana García">
      </div>
      <div class="form-grupo">
        <label>Grado y sección del estudiante</label>
        <input type="text" id="solGrado" placeholder="Ej. 2do Año Bachillerato — Sección A">
      </div>
      <div class="panel-modal-info">
        <svg class="lucide" data-lucide="info"></svg>
        Tu solicitud será revisada por la secretaría académica. Recibirás tus credenciales de acceso en el correo proporcionado en un plazo de 1 a 2 días hábiles.
      </div>
      <button type="button" class="btn-primario" id="btnEnviarSolicitud">
        <svg class="lucide" data-lucide="send"></svg> Enviar solicitud
      </button>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════
       PANEL: Olvidé mi contraseña
  ════════════════════════════════════════════════ -->
  <div class="panel-modal-scrim" id="scrimPassword"></div>
  <div class="panel-modal" id="panelPassword" role="dialog" aria-modal="true" aria-labelledby="tituloPassword">
    <div class="panel-modal-header">
      <div class="panel-modal-icono bg-dorado"><svg class="lucide" data-lucide="key-round"></svg></div>
      <div>
        <h2 id="tituloPassword">Recuperar contraseña</h2>
        <p>Indica tu usuario o correo y te ayudaremos a recuperar el acceso.</p>
      </div>
      <button type="button" class="panel-modal-cerrar" id="cerrarPassword" aria-label="Cerrar">
        <svg class="lucide" data-lucide="x"></svg>
      </button>
    </div>
    <div class="panel-modal-body">
      <div class="form-grupo">
        <label>Usuario o correo registrado</label>
        <input type="text" id="recUsuario" placeholder="Ej. mgarcia  ó  correo@ejemplo.com" autocomplete="username">
      </div>
      <div class="panel-modal-info">
        <svg class="lucide" data-lucide="info"></svg>
        El administrador verificará tu identidad y te enviará una contraseña temporal al correo vinculado a tu cuenta.
      </div>
      <div class="panel-modal-pasos">
        <div class="paso">
          <div class="paso-num">1</div>
          <span>Envía tu solicitud con el formulario de arriba</span>
        </div>
        <div class="paso">
          <div class="paso-num">2</div>
          <span>La secretaría valida tu identidad (1–2 días hábiles)</span>
        </div>
        <div class="paso">
          <div class="paso-num">3</div>
          <span>Recibes una contraseña temporal en tu correo</span>
        </div>
      </div>
      <button type="button" class="btn-primario" id="btnEnviarRecuperacion">
        <svg class="lucide" data-lucide="send"></svg> Solicitar recuperación
      </button>
    </div>
  </div>

  </div>


<script>
function mostrarTab(rol) {
  document.getElementById('tabPadre').classList.toggle('activo', rol === 'padre');
  document.getElementById('tabAdmin').classList.toggle('activo', rol === 'admin');
  document.getElementById('formPadre').style.display = rol === 'padre' ? 'block' : 'none';
  document.getElementById('formAdmin').style.display = rol === 'admin' ? 'block' : 'none';
  var ayuda = document.getElementById('ayudaPadre');
  if (ayuda) ayuda.style.display = rol === 'padre' ? 'flex' : 'none';
}

/* ── Paneles modales ── */
function abrirPanel(panelId, scrimId) {
  var panel = document.getElementById(panelId);
  var scrim = document.getElementById(scrimId);
  if (!panel || !scrim) return;
  scrim.classList.add('visible');
  panel.classList.add('visible');
  document.body.style.overflow = 'hidden';
}

function cerrarPanel(panelId, scrimId) {
  var panel = document.getElementById(panelId);
  var scrim = document.getElementById(scrimId);
  if (!panel || !scrim) return;
  scrim.classList.remove('visible');
  panel.classList.remove('visible');
  document.body.style.overflow = '';
}

function mostrarExito(btn, msg) {
  var original = btn.innerHTML;
  btn.innerHTML = '<svg class="lucide" data-lucide="check-circle"></svg> ' + msg;
  btn.style.background = 'var(--verde)';
  btn.disabled = true;
  if (window.lucide) window.lucide.createIcons();
  setTimeout(function () {
    btn.innerHTML = original;
    btn.style.background = '';
    btn.disabled = false;
    if (window.lucide) window.lucide.createIcons();
  }, 3000);
}

document.addEventListener('DOMContentLoaded', function () {
  /* Solicitar cuenta */
  var btnSol = document.getElementById('btnSolicitarCuenta');
  if (btnSol) btnSol.addEventListener('click', function () { abrirPanel('panelSolicitar', 'scrimSolicitar'); });
  document.getElementById('cerrarSolicitar').addEventListener('click', function () { cerrarPanel('panelSolicitar', 'scrimSolicitar'); });
  document.getElementById('scrimSolicitar').addEventListener('click', function () { cerrarPanel('panelSolicitar', 'scrimSolicitar'); });
  document.getElementById('btnEnviarSolicitud').addEventListener('click', function () {
    mostrarExito(this, '¡Solicitud enviada!');
    setTimeout(function () { cerrarPanel('panelSolicitar', 'scrimSolicitar'); }, 1200);
  });

  /* Olvidé contraseña */
  var btnPass = document.getElementById('btnOlvidePassword');
  if (btnPass) btnPass.addEventListener('click', function () { abrirPanel('panelPassword', 'scrimPassword'); });
  document.getElementById('cerrarPassword').addEventListener('click', function () { cerrarPanel('panelPassword', 'scrimPassword'); });
  document.getElementById('scrimPassword').addEventListener('click', function () { cerrarPanel('panelPassword', 'scrimPassword'); });
  document.getElementById('btnEnviarRecuperacion').addEventListener('click', function () {
    mostrarExito(this, '¡Solicitud enviada!');
    setTimeout(function () { cerrarPanel('panelPassword', 'scrimPassword'); }, 1200);
  });

  /* Escape cierra ambos paneles */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      cerrarPanel('panelSolicitar', 'scrimSolicitar');
      cerrarPanel('panelPassword', 'scrimPassword');
    }
  });
});
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
