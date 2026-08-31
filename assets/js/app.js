// Portal de Notas — comportamiento de interfaz (100% visual, no toca lógica de negocio)
(function () {
  'use strict';

  /* ---------- Tema claro / oscuro ---------- */
  var temaGuardado = localStorage.getItem('pn_tema');
  var prefiereOscuro = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  var temaInicial = temaGuardado || (prefiereOscuro ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme', temaInicial);

  function alternarTema() {
    var actual = document.documentElement.getAttribute('data-theme');
    var nuevo = actual === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', nuevo);
    localStorage.setItem('pn_tema', nuevo);
  }

  /* ---------- Sidebar colapsable (escritorio) ---------- */
  var colapsado = localStorage.getItem('pn_sidebar_colapsado') === '1';
  if (colapsado) document.body.classList.add('sidebar-colapsado');

  function alternarSidebar() {
    document.body.classList.toggle('sidebar-colapsado');
    localStorage.setItem('pn_sidebar_colapsado', document.body.classList.contains('sidebar-colapsado') ? '1' : '0');
  }

  function alternarSidebarMovil() {
    document.body.classList.toggle('sidebar-movil-abierto');
  }

  /* ---------- Reloj y fecha en tiempo real ---------- */
  var DIAS = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
  var MESES = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

  function actualizarReloj() {
    var ahora = new Date();
    var horaEl = document.getElementById('pnHoraActual');
    var fechaEl = document.getElementById('pnFechaActual');
    if (horaEl) {
      var h = ahora.getHours(), m = ahora.getMinutes(), s = ahora.getSeconds();
      var pad = function (n) { return n < 10 ? '0' + n : n; };
      horaEl.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
    }
    if (fechaEl) {
      fechaEl.textContent = DIAS[ahora.getDay()] + ', ' + ahora.getDate() + ' de ' + MESES[ahora.getMonth()];
    }
  }

  /* ---------- Menús desplegables (usuario / notificaciones) ---------- */
  function cerrarMenusAbiertos(exceptoEl) {
    document.querySelectorAll('.menu-usuario.abierto, .centro-notif.abierto').forEach(function (el) {
      if (el !== exceptoEl) el.classList.remove('abierto');
    });
  }

  function initMenus() {
    document.querySelectorAll('[data-menu-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var contenedor = btn.closest('.menu-usuario, .centro-notif');
        if (!contenedor) return;
        var estabaAbierto = contenedor.classList.contains('abierto');
        cerrarMenusAbiertos();
        contenedor.classList.toggle('abierto', !estabaAbierto);
        if (window.lucide) window.lucide.createIcons();
      });
    });
    document.querySelectorAll('.dropdown').forEach(function (dd) {
      dd.addEventListener('click', function (e) {
        if (!e.target.closest('a')) {
          e.stopPropagation();
        }
      });
    });
    document.addEventListener('click', function () { cerrarMenusAbiertos(); });
  }

  /* ---------- Mostrar / ocultar contraseña ---------- */
  function initTogglePassword() {
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = btn.previousElementSibling;
        if (!input) return;
        var esOculto = input.type === 'password';
        input.type = esOculto ? 'text' : 'password';
        btn.innerHTML = esOculto
          ? '<svg class="lucide" data-lucide="eye-off"></svg>'
          : '<svg class="lucide" data-lucide="eye"></svg>';
        if (window.lucide) window.lucide.createIcons();
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) window.lucide.createIcons();

    var toggleBtn = document.getElementById('pnSidebarToggle');
    if (toggleBtn) toggleBtn.addEventListener('click', alternarSidebar);

    var toggleMovilBtn = document.getElementById('pnSidebarToggleMovil');
    if (toggleMovilBtn) toggleMovilBtn.addEventListener('click', alternarSidebarMovil);

    var scrim = document.querySelector('.sidebar-scrim');
    if (scrim) scrim.addEventListener('click', alternarSidebarMovil);

    var themeBtn = document.getElementById('pnThemeSwitch');
    if (themeBtn) themeBtn.addEventListener('click', alternarTema);

    initMenus();
    initTogglePassword();
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
  });
})();
