<?php
require_once __DIR__ . '/includes/sesion.php';

// Si ya hay sesión activa, redirigir directo
if (!empty($_SESSION['admin_id']))    { header('Location: admin/dashboard.php'); exit; }
if (!empty($_SESSION['padre_id']))    { header('Location: padres/dashboard.php'); exit; }
if (!empty($_SESSION['profesor_id'])) { header('Location: profesor/dashboard.php'); exit; }

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
<title>Portal de Notas </title>
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
      <h1><svg class="lucide" data-lucide="graduation-cap"></svg> SIGEA | Portal Web de Consulta de Notas</h1>
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
        <button type="button" id="tabProfesor" onclick="mostrarTab('profesor')"><svg class="lucide" data-lucide="presentation"></svg> Profesor</button>
        <button type="button" id="tabAdmin" onclick="mostrarTab('admin')"><svg class="lucide" data-lucide="shield-check"></svg> Administrador</button>
      </div>

      <!-- ── FORM: Padre ── -->
      <form id="formPadre" method="POST" action="login_procesar.php">
        <input type="hidden" name="rol" value="padre">
        <div class="form-grupo">
          <label>Usuario</label>
          <input type="text" name="usuario" placeholder="Ingresa tu usuario" required autofocus>
        </div>
        <div class="form-grupo">
          <label>Contraseña</label>
          <div class="campo-password">
            <input type="password" name="password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" required>
            <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña"><svg class="lucide" data-lucide="eye"></svg></button>
          </div>
        </div>
        <label class="recordarme"><input type="checkbox" name="recordarme"> Recordarme en este dispositivo</label>
        <button type="submit" class="btn-primario"><svg class="lucide" data-lucide="log-in"></svg> Entrar como Padre</button>
      </form>

     <div class="login-ayuda" id="ayudaPadre">
    <div class="login-ayuda-links">
        <button type="button" class="link-ayuda" id="btnOlvidePassword">
            <svg class="lucide" data-lucide="key-round"></svg>
            ¿Olvidaste tu contraseña?
        </button>
    </div>
</div>

      <!-- ── FORM: Profesor ── -->
      <form id="formProfesor" method="POST" action="login_procesar.php" style="display:none">
        <input type="hidden" name="rol" value="profesor">
        <div class="form-grupo">
          <label>Usuario</label>
          <input type="text" name="usuario" placeholder="Ingresa tu usuario">
        </div>
        <div class="form-grupo">
          <label>Contraseña</label>
          <div class="campo-password">
            <input type="password" name="password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
            <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña"><svg class="lucide" data-lucide="eye"></svg></button>
          </div>
        </div>
        <label class="recordarme"><input type="checkbox" name="recordarme"> Recordarme en este dispositivo</label>
        <button type="submit" class="btn-primario"><svg class="lucide" data-lucide="log-in"></svg> Entrar como Profesor</button>
      </form>

      <!-- ── FORM: Admin ── -->
      <form id="formAdmin" method="POST" action="login_procesar.php" style="display:none">
        <input type="hidden" name="rol" value="admin">
        <div class="form-grupo">
          <label>Usuario</label>
          <input type="text" name="usuario" placeholder="Ingresa tu usuario">
        </div>
        <div class="form-grupo">
          <label>Contraseña</label>
          <div class="campo-password">
            <input type="password" name="password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
            <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña"><svg class="lucide" data-lucide="eye"></svg></button>
          </div>
        </div>
        <label class="recordarme"><input type="checkbox" name="recordarme"> Recordarme en este dispositivo</label>
        <button type="submit" class="btn-primario"><svg class="lucide" data-lucide="log-in"></svg> Entrar como Administrador</button>
      </form>
    </div><!-- /login-form-side -->
  </div><!-- /login-card -->

  <!-- ═══════════════════════════════════════════════
       PANEL: Solicitar cuenta de padre
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
        <label>Carnet del estudiante</label>
        <input type="text" id="solCarnet" placeholder="Ej. 2026-001">
      </div>
      <div class="form-grupo">
        <label>Nombre del estudiante</label>
        <input type="text" id="solEstudiante" placeholder="Ej. Ana García">
      </div>
      <div class="panel-modal-info">
        <svg class="lucide" data-lucide="info"></svg>
        Tu solicitud será revisada por la secretaría académica. Recibirás tus credenciales de acceso en el correo proporcionado en un plazo de 1 a 2 días hábiles.
      </div>
      <button type="button" class="btn-primario" id="btnEnviarSolicitud">
        <svg class="lucide" data-lucide="send"></svg> Enviar solicitud
      </button>
    </div><!-- /panel-modal-body -->
  </div><!-- /panelSolicitar -->

  <!-- ═══════════════════════════════════════════════
       PANEL: Recuperar contraseña
  ════════════════════════════════════════════════ -->
  <div class="panel-modal-scrim" id="scrimPassword"></div>
  <div class="panel-modal" id="panelPassword" role="dialog" aria-modal="true" aria-labelledby="tituloPassword">
    <div class="panel-modal-header">
      <div class="panel-modal-icono bg-dorado"><svg class="lucide" data-lucide="key-round"></svg></div>
      <div>
        <h2 id="tituloPassword">Recuperar contraseña</h2>
        <p>Indica tu correo registrado y la secretaría te ayudará.</p>
      </div>
      <button type="button" class="panel-modal-cerrar" id="cerrarPassword" aria-label="Cerrar">
        <svg class="lucide" data-lucide="x"></svg>
      </button>
    </div>
    <div class="panel-modal-body">
      <div class="form-grupo">
        <label>Eres:</label>
        <select id="recTipo" style="width:100%;padding:11px 14px;border:1.5px solid var(--borde);border-radius:10px;background:var(--tarjeta-solida);color:var(--texto);">
          <option value="padre">Padre de familia</option>
          <option value="admin">Profesor / Administrador</option>
        </select>
      </div>
      <div class="form-grupo">
        <label>Correo electrónico registrado</label>
        <input type="email" id="recCorreo" placeholder="correo@ejemplo.com" autocomplete="email">
      </div>
      <div class="panel-modal-info">
        <svg class="lucide" data-lucide="info"></svg>
        La secretaría verificará tu identidad y restablecerá tu contraseña. Recibirás un correo con los pasos a seguir.
      </div>
      <button type="button" class="btn-primario" id="btnEnviarRecuperacion">
        <svg class="lucide" data-lucide="send"></svg> Solicitar recuperación
      </button>
    </div><!-- /panel-modal-body -->
  </div><!-- /panelPassword -->
</div><!-- /login-wrap -->

<script>
  lucide.createIcons();

  function mostrarTab(rol) {
    const tabs = { padre: 'formPadre', profesor: 'formProfesor', admin: 'formAdmin' };
    const botones = { padre: 'tabPadre', profesor: 'tabProfesor', admin: 'tabAdmin' };

    Object.keys(tabs).forEach(key => {
      document.getElementById(tabs[key]).style.display = (key === rol) ? '' : 'none';
      document.getElementById(botones[key]).classList.toggle('activo', key === rol);
    });

    document.getElementById('ayudaPadre').style.display = (rol === 'padre') ? '' : 'none';
  }

  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.previousElementSibling;
      const esPassword = input.type === 'password';
      input.type = esPassword ? 'text' : 'password';
      btn.innerHTML = `<svg class="lucide" data-lucide="${esPassword ? 'eye-off' : 'eye'}"></svg>`;
      lucide.createIcons();
    });
  });

  function abrirPanel(idPanel, idScrim) {
    document.getElementById(idPanel).classList.add('visible');
    document.getElementById(idScrim).classList.add('visible');
  }
  function cerrarPanel(idPanel, idScrim) {
    document.getElementById(idPanel).classList.remove('visible');
    document.getElementById(idScrim).classList.remove('visible');
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

  function mostrarErrorEnvio(btn, msg) {
    var original = btn.innerHTML;
    btn.innerHTML = '<svg class="lucide" data-lucide="alert-circle"></svg> ' + msg;
    if (window.lucide) window.lucide.createIcons();
    setTimeout(function () {
      btn.innerHTML = original;
      if (window.lucide) window.lucide.createIcons();
    }, 2500);
  }

  document.addEventListener('DOMContentLoaded', function () {
    var btnSol = document.getElementById('btnSolicitarCuenta');
    if (btnSol) btnSol.addEventListener('click', function () { abrirPanel('panelSolicitar', 'scrimSolicitar'); });
    document.getElementById('cerrarSolicitar').addEventListener('click', function () { cerrarPanel('panelSolicitar', 'scrimSolicitar'); });
    document.getElementById('scrimSolicitar').addEventListener('click', function () { cerrarPanel('panelSolicitar', 'scrimSolicitar'); });
    document.getElementById('btnEnviarSolicitud').addEventListener('click', function () {
      var btn = this;
      var datos = new FormData();
      datos.append('nombre', document.getElementById('solNombre').value.trim());
      datos.append('correo', document.getElementById('solCorreo').value.trim());
      datos.append('telefono', document.getElementById('solTelefono').value.trim());
      datos.append('carnet', document.getElementById('solCarnet').value.trim());
      datos.append('estudiante', document.getElementById('solEstudiante').value.trim());

      fetch('solicitud_cuenta.php', { method: 'POST', body: datos })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.ok) {
            mostrarExito(btn, '¡Solicitud enviada!');
            setTimeout(function () { cerrarPanel('panelSolicitar', 'scrimSolicitar'); }, 1200);
          } else {
            mostrarErrorEnvio(btn, data.error || 'No se pudo enviar.');
          }
        })
        .catch(function () { mostrarErrorEnvio(btn, 'Error de conexión.'); });
    });

    var btnPass = document.getElementById('btnOlvidePassword');
    if (btnPass) btnPass.addEventListener('click', function () { abrirPanel('panelPassword', 'scrimPassword'); });
    document.getElementById('cerrarPassword').addEventListener('click', function () { cerrarPanel('panelPassword', 'scrimPassword'); });
    document.getElementById('scrimPassword').addEventListener('click', function () { cerrarPanel('panelPassword', 'scrimPassword'); });
    document.getElementById('btnEnviarRecuperacion').addEventListener('click', function () {
      var btn = this;
      var datos = new FormData();
      datos.append('tipo', document.getElementById('recTipo').value);
      datos.append('correo', document.getElementById('recCorreo').value.trim());

      fetch('solicitud_password.php', { method: 'POST', body: datos })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.ok) {
            mostrarExito(btn, '¡Solicitud enviada!');
            setTimeout(function () { cerrarPanel('panelPassword', 'scrimPassword'); }, 1200);
          } else {
            mostrarErrorEnvio(btn, data.error || 'No se pudo enviar.');
          }
        })
        .catch(function () { mostrarErrorEnvio(btn, 'Error de conexión.'); });
    });

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