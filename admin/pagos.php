<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirCajero();

$mensaje = '';
$errores = [];

/** Genera el siguiente correlativo tipo REC-000123 de forma segura dentro de una transacción. */
function generarCorrelativo(PDO $pdo): string {
    $stmt = $pdo->query("SELECT correlativo FROM pagos ORDER BY id DESC LIMIT 1");
    $ultimo = $stmt->fetchColumn();
    $siguiente = $ultimo ? ((int)substr($ultimo, 4)) + 1 : 1;
    return 'REC-' . str_pad((string)$siguiente, 6, '0', STR_PAD_LEFT);
}

// Anular pago (append-only: nunca se elimina, se marca Anulado con motivo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['anular_id'])) {
    $idAnular = (int)$_POST['anular_id'];
    $motivo   = trim($_POST['motivo_anulacion'] ?? '');
    if ($motivo === '') {
        $mensaje = 'Debes indicar un motivo para anular el pago.';
    } else {
        $stmt = $pdo->prepare("UPDATE pagos SET estado='Anulado', motivo_anulacion=?, id_anulador=?, anulado_en=NOW() WHERE id=? AND estado='Activo'");
        $stmt->execute([$motivo, $_SESSION['admin_id'], $idAnular]);
        header('Location: pagos.php?ok=anulado');
        exit;
    }
}

// Registrar nuevo pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pago'])) {
    $idPadre        = (int)($_POST['id_padre'] ?? 0);
    $concepto       = trim($_POST['concepto'] ?? '');
    $metodo         = $_POST['metodo_pago'] ?? '';
    $referencia     = trim($_POST['referencia'] ?? '');
    $sede           = trim($_POST['sede'] ?? '') ?: 'Sede Central';
    $idsEstudiantes = $_POST['estudiantes'] ?? [];       // array de IDs seleccionados
    $montos         = $_POST['monto'] ?? [];             // array id_estudiante => monto

    $metodosValidos = ['efectivo', 'transferencia', 'tarjeta_debito', 'tarjeta_credito', 'paypal'];

    if (!$idPadre) $errores[] = 'Selecciona el padre/tutor que realiza el pago.';
    if ($concepto === '') $errores[] = 'Indica el concepto del pago.';
    if (!in_array($metodo, $metodosValidos, true)) $errores[] = 'Selecciona un método de pago válido.';
    if (empty($idsEstudiantes)) $errores[] = 'Selecciona al menos un estudiante.';
    if (in_array($metodo, ['transferencia', 'tarjeta_debito', 'tarjeta_credito', 'paypal'], true) && $referencia === '') {
        $errores[] = 'Indica el número de referencia/transacción para este método de pago.';
    }

    $detalle = [];
    $montoTotal = 0.0;
    foreach ($idsEstudiantes as $idEst) {
        $idEst = (int)$idEst;
        $m = (float)($montos[$idEst] ?? 0);
        if ($m <= 0) {
            $errores[] = 'El monto de cada estudiante seleccionado debe ser mayor a 0.';
            break;
        }
        $detalle[] = ['id_estudiante' => $idEst, 'monto' => $m];
        $montoTotal += $m;
    }

    if (!$errores) {
        try {
            $pdo->beginTransaction();
            $correlativo = generarCorrelativo($pdo);
            $stmt = $pdo->prepare('INSERT INTO pagos (correlativo, id_padre, concepto, metodo_pago, referencia, monto_total, sede, id_operador) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$correlativo, $idPadre, $concepto, $metodo, $referencia ?: null, $montoTotal, $sede, $_SESSION['admin_id']]);
            $idPago = (int)$pdo->lastInsertId();

            $stmtDet = $pdo->prepare('INSERT INTO pago_detalle (id_pago, id_estudiante, monto) VALUES (?,?,?)');
            foreach ($detalle as $d) {
                $stmtDet->execute([$idPago, $d['id_estudiante'], $d['monto']]);
            }
            $pdo->commit();
            header('Location: recibo_pago.php?id=' . $idPago);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errores[] = 'Ocurrió un error al registrar el pago. Intenta nuevamente.';
        }
    }
}

// Datos para el formulario
$padresDisponibles = $pdo->query('SELECT id, nombre, apellido FROM padres ORDER BY nombre, apellido')->fetchAll();
$estudiantesPorPadre = $pdo->query('SELECT id, id_padre, nombre, apellido, grado, seccion FROM estudiantes ORDER BY nombre')->fetchAll();

// Historial de pagos (más recientes primero)
$pagos = $pdo->query("
    SELECT p.*, pa.nombre AS padre_nombre, pa.apellido AS padre_apellido,
           GROUP_CONCAT(e.nombre SEPARATOR ', ') AS estudiantes_nombres
    FROM pagos p
    JOIN padres pa ON pa.id = p.id_padre
    LEFT JOIN pago_detalle pd ON pd.id_pago = p.id
    LEFT JOIN estudiantes e ON e.id = pd.id_estudiante
    GROUP BY p.id
    ORDER BY p.fecha_pago DESC
    LIMIT 100
")->fetchAll();

$etiquetasMetodo = [
    'efectivo'         => 'Efectivo',
    'transferencia'    => 'Transferencia bancaria',
    'tarjeta_debito'   => 'Tarjeta de débito',
    'tarjeta_credito'  => 'Tarjeta de crédito',
    'paypal'           => 'PayPal',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagos de Mensualidad | Portal de Notas</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://unpkg.com/lucide@latest"></script>
<style>
  .estudiantes-pago { border: 1px solid var(--borde); border-radius: 12px; padding: 14px 16px; margin-top: 4px; }
  .fila-estudiante-pago { display: grid; grid-template-columns: auto 1fr 140px; gap: 12px; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--borde); }
  .fila-estudiante-pago:last-child { border-bottom: none; }
  .fila-estudiante-pago input[type="number"] { margin: 0; }
  .fila-estudiante-pago.oculto { display: none; }
  .total-pago { text-align: right; font-size: 15px; font-weight: 700; margin-top: 10px; }
  .badge-estado.Activo { background: rgba(48, 209, 88, 0.14); color: #1c8a45; }
  [data-theme="dark"] .badge-estado.Activo { color: #7FE79A; }
  .badge-estado.Anulado { background: rgba(255, 69, 58, 0.14); color: #c9372c; }
  [data-theme="dark"] .badge-estado.Anulado { color: #FF8A80; }
</style>
</head>
<body>
<div class="fondo-ambiental"></div>
<div class="app-shell">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Pagos de Mensualidad'; include __DIR__ . '/../includes/topbar.php'; ?>

    <?php if (isset($_GET['ok']) && $_GET['ok'] === 'anulado'): ?>
      <div class="alerta alerta-ok"><svg class="lucide" data-lucide="check-circle"></svg> Pago anulado correctamente.</div>
    <?php endif; ?>
    <?php if ($mensaje): ?>
      <div class="alerta alerta-error"><svg class="lucide" data-lucide="alert-circle"></svg> <?= h($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($errores): ?>
      <div class="alerta alerta-error">
        <svg class="lucide" data-lucide="alert-circle"></svg>
        <ul style="margin: 8px 0 0 20px; padding: 0;">
          <?php foreach ($errores as $err): ?>
            <li><?= h($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="credit-card"></svg> Registrar pago de mensualidad</h2>
      <form method="POST" id="formPago">
        <input type="hidden" name="registrar_pago" value="1">
        <div class="form-inline-grid">
          <div class="form-grupo">
            <label>Padre/Tutor que paga</label>
            <select name="id_padre" id="selPadre" required>
              <option value="">Selecciona...</option>
              <?php foreach ($padresDisponibles as $p): ?>
                <option value="<?= $p['id'] ?>"><?= h($p['nombre'] . ' ' . $p['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label>Concepto</label>
            <input type="text" name="concepto" placeholder="Ej. Mensualidad Agosto 2026" required>
          </div>
          <div class="form-grupo">
            <label>Método de pago</label>
            <select name="metodo_pago" id="selMetodo" required>
              <option value="">Selecciona...</option>
              <option value="efectivo">Efectivo</option>
              <option value="transferencia">Transferencia bancaria</option>
              <option value="tarjeta_debito">Tarjeta de débito</option>
              <option value="tarjeta_credito">Tarjeta de crédito</option>
              <option value="paypal">PayPal</option>
            </select>
          </div>
          <div class="form-grupo" id="grupoReferencia">
            <label>Referencia / # de transacción</label>
            <input type="text" name="referencia" placeholder="No ingreses el número completo de tarjeta">
          </div>
          <div class="form-grupo">
            <label>Sede</label>
            <input type="text" name="sede" value="Sede Central">
          </div>
        </div>

        <div class="form-grupo" style="margin-top:16px;">
          <label>Estudiantes a los que aplica el pago</label>
          <div class="estudiantes-pago" id="listaEstudiantes">
            <p id="avisoSinPadre" style="color:var(--texto-secundario); font-size:13.5px; margin:4px 0;">Selecciona primero un padre/tutor.</p>
            <?php foreach ($estudiantesPorPadre as $e): ?>
              <div class="fila-estudiante-pago oculto" data-id-padre="<?= $e['id_padre'] ?>">
                <input type="checkbox" name="estudiantes[]" value="<?= $e['id'] ?>" class="chkEstudiante" data-monto-target="monto_<?= $e['id'] ?>">
                <span><?= h($e['nombre'] . ' ' . $e['apellido']) ?> — <?= h($e['grado'] . ' ' . $e['seccion']) ?></span>
                <input type="number" step="0.01" min="0" name="monto[<?= $e['id'] ?>]" id="monto_<?= $e['id'] ?>" placeholder="Monto" disabled>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="total-pago">Total: $<span id="totalPago">0.00</span></div>
        </div>

        <div class="form-grupo" style="margin-top:16px;">
          <button type="submit" class="btn-primario"><svg class="lucide" data-lucide="save"></svg> Registrar pago y generar recibo</button>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2><svg class="lucide" data-lucide="history"></svg> Historial de pagos</h2>
      <table class="tabla-datos">
        <thead>
          <tr><th>Recibo</th><th>Fecha</th><th>Tutor</th><th>Estudiante(s)</th><th>Método</th><th>Monto</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($pagos as $pg): ?>
          <tr>
            <td><?= h($pg['correlativo']) ?></td>
            <td><?= h(date('d/m/Y H:i', strtotime($pg['creado_en']))) ?></td>
            <td><?= h($pg['padre_nombre'] . ' ' . $pg['padre_apellido']) ?></td>
            <td><?= h($pg['estudiantes_nombres'] ?? '') ?></td>
            <td><?= h($etiquetasMetodo[$pg['metodo_pago']] ?? $pg['metodo_pago']) ?></td>
            <td>$<?= number_format((float)$pg['monto_total'], 2) ?></td>
            <td><span class="badge-estado <?= h($pg['estado']) ?>"><?= h($pg['estado']) ?></span></td>
            <td>
              <a class="btn-sm" href="recibo_pago.php?id=<?= $pg['id'] ?>" title="Ver recibo"><svg class="lucide" data-lucide="receipt"></svg></a>
              <?php if ($pg['estado'] === 'Activo'): ?>
                <button type="button" class="btn-sm btn-eliminar" title="Anular" data-pago-id="<?= (int)$pg['id'] ?>" data-correlativo="<?= h($pg['correlativo']) ?>" onclick="abrirAnulacion(this)"><svg class="lucide" data-lucide="ban"></svg></button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$pagos): ?>
          <tr><td colspan="8">Aún no se han registrado pagos.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Formulario oculto de anulación (append-only: nunca se elimina el registro) -->
<form method="POST" id="formAnular" style="display:none;">
  <input type="hidden" name="anular_id" id="anularId">
  <input type="hidden" name="motivo_anulacion" id="anularMotivo">
</form>

<script src="../assets/js/app.js"></script>
<script>
  const selPadre = document.getElementById('selPadre');
  const filas = document.querySelectorAll('.fila-estudiante-pago');
  const avisoSinPadre = document.getElementById('avisoSinPadre');
  const totalSpan = document.getElementById('totalPago');
  const selMetodo = document.getElementById('selMetodo');
  const grupoReferencia = document.getElementById('grupoReferencia');

  selPadre.addEventListener('change', function () {
    const idPadre = this.value;
    avisoSinPadre.style.display = idPadre ? 'none' : 'block';
    filas.forEach(fila => {
      const coincide = fila.dataset.idPadre === idPadre;
      fila.classList.toggle('oculto', !coincide);
      const chk = fila.querySelector('.chkEstudiante');
      const monto = fila.querySelector('input[type="number"]');
      if (!coincide) { chk.checked = false; monto.disabled = true; monto.value = ''; }
    });
    calcularTotal();
  });

  document.getElementById('listaEstudiantes').addEventListener('change', function (e) {
    if (e.target.classList.contains('chkEstudiante')) {
      const montoInput = document.getElementById(e.target.dataset.montoTarget);
      montoInput.disabled = !e.target.checked;
      if (!e.target.checked) montoInput.value = '';
    }
    calcularTotal();
  });
  document.getElementById('listaEstudiantes').addEventListener('input', calcularTotal);

  function calcularTotal() {
    let total = 0;
    document.querySelectorAll('.chkEstudiante:checked').forEach(chk => {
      const montoInput = document.getElementById(chk.dataset.montoTarget);
      total += parseFloat(montoInput.value) || 0;
    });
    totalSpan.textContent = total.toFixed(2);
  }

  selMetodo.addEventListener('change', function () {
    const requiereReferencia = ['transferencia', 'tarjeta_debito', 'tarjeta_credito', 'paypal'].includes(this.value);
    grupoReferencia.style.display = requiereReferencia || this.value === '' ? 'block' : 'none';
  });

  function abrirAnulacion(btn) {
    const id = btn.dataset.pagoId;
    const correlativo = btn.dataset.correlativo;
    const motivo = prompt('Motivo de anulación del recibo ' + correlativo + ':');
    if (motivo && motivo.trim() !== '') {
      document.getElementById('anularId').value = id;
      document.getElementById('anularMotivo').value = motivo.trim();
      document.getElementById('formAnular').submit();
    }
  }
</script>
</body>
</html>