<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
exigirCajero();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.*, pa.nombre AS padre_nombre, pa.apellido AS padre_apellido, pa.correo AS padre_correo,
           op.nombre AS operador_nombre, op.rol AS operador_rol
    FROM pagos p
    JOIN padres pa ON pa.id = p.id_padre
    JOIN administradores op ON op.id = p.id_admin
    WHERE p.id = ?
");
$stmt->execute([$id]);
$pago = $stmt->fetch();

if (!$pago) {
    header('Location: pagos.php');
    exit;
}

$stmtDet = $pdo->prepare("
    SELECT pd.monto, e.nombre, e.apellido, e.carnet, e.grado, e.seccion
    FROM pago_detalle pd
    JOIN estudiantes e ON e.id = pd.id_estudiante
    WHERE pd.id_pago = ?
");
$stmtDet->execute([$id]);
$detalle = $stmtDet->fetchAll();

$etiquetasMetodo = [
    'Efectivo'      => 'Efectivo',
    'Transferencia' => 'Transferencia bancaria',
    'Tarjeta'       => 'Tarjeta',
    'QR'            => 'Pago con QR',
    'PayPal'        => 'PayPal',
    'Visa'          => 'Visa',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recibo <?= h($pago['folio']) ?> | Portal de Notas</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://unpkg.com/lucide@latest"></script>
<style>
  .recibo-wrap { max-width: 640px; margin: 0 auto; }
  .recibo { border: 1px solid var(--borde); border-radius: 14px; padding: 32px; background: var(--superficie); }
  .recibo-cabecera { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--borde); padding-bottom: 16px; margin-bottom: 20px; }
  .recibo-cabecera h1 { font-size: 19px; margin: 0 0 4px; }
  .recibo-folio { font-size: 15px; font-weight: 700; color: var(--azul); }
  .recibo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 24px; margin-bottom: 20px; font-size: 13.5px; }
  .recibo-grid div span.rotulo { display: block; color: var(--texto-secundario); font-size: 11.5px; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px; }
  .recibo table { width: 100%; border-collapse: collapse; font-size: 13.5px; margin-bottom: 16px; }
  .recibo table th { text-align: left; padding: 8px 6px; border-bottom: 2px solid var(--borde); color: var(--texto-secundario); font-size: 11.5px; text-transform: uppercase; }
  .recibo table td { padding: 8px 6px; border-bottom: 1px solid var(--borde); }
  .recibo-total { text-align: right; font-size: 17px; font-weight: 800; margin-top: 6px; }
  .recibo-pie { margin-top: 24px; padding-top: 14px; border-top: 1px solid var(--borde); font-size: 12px; color: var(--texto-secundario); text-align: center; }
  .anulado-marca { color: #c9372c; font-weight: 800; text-align: center; font-size: 20px; border: 3px solid #c9372c; border-radius: 10px; padding: 8px; margin-bottom: 18px; transform: rotate(-2deg); }
  .acciones-recibo { display: flex; gap: 10px; margin: 18px auto; max-width: 640px; }
  @media print {
    .app-shell, .acciones-recibo, .fondo-ambiental, aside, header.topbar { display: none !important; }
    .recibo-wrap { max-width: 100%; }
    .recibo { border: none; padding: 0; }
    body { background: #fff; }
  }
</style>
</head>
<body>
<div class="fondo-ambiental"></div>
<div class="app-shell">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <main class="contenido">
    <?php $tituloPagina = 'Recibo de Pago'; include __DIR__ . '/../includes/topbar.php'; ?>

    <div class="acciones-recibo">
      <a href="pagos.php" class="btn-sm"><svg class="lucide" data-lucide="arrow-left"></svg> Volver</a>
      <button type="button" class="btn-primario" onclick="window.print()"><svg class="lucide" data-lucide="printer"></svg> Imprimir / Guardar como PDF</button>
    </div>

    <div class="recibo-wrap">
      <div class="recibo">
        <?php if ($pago['estado'] === 'Anulado'): ?>
          <div class="anulado-marca">RECIBO ANULADO</div>
        <?php endif; ?>

        <div class="recibo-cabecera">
          <div>
            <h1>Portal de Notas</h1>
          </div>
          <div style="text-align:right;">
            <div class="recibo-folio"><?= h($pago['folio']) ?></div>
            <div style="font-size:12.5px; color:var(--texto-secundario);"><?= h(date('d/m/Y H:i:s', strtotime($pago['fecha_pago']))) ?> (<?= date_default_timezone_get() ?>)</div>
          </div>
        </div>

        <div class="recibo-grid">
          <div><span class="rotulo">Tutor / Cliente</span><?= h($pago['padre_nombre'] . ' ' . $pago['padre_apellido']) ?></div>
          <div><span class="rotulo">Correo</span><?= h($pago['padre_correo'] ?: '—') ?></div>
          <div><span class="rotulo">Método de pago</span><?= h($etiquetasMetodo[$pago['metodo_pago']] ?? $pago['metodo_pago']) ?></div>
          <div><span class="rotulo">Referencia</span><?= h($pago['referencia'] ?: '—') ?></div>
          <div><span class="rotulo">Registrado por</span><?= h($pago['operador_nombre']) ?> (<?= h(ucfirst($pago['operador_rol'])) ?>)</div>
        </div>

        <table>
          <thead><tr><th>Estudiante</th><th>Carnet</th><th>Grado/Sección</th><th style="text-align:right;">Monto</th></tr></thead>
          <tbody>
          <?php foreach ($detalle as $d): ?>
            <tr>
              <td><?= h($d['nombre'] . ' ' . $d['apellido']) ?></td>
              <td><?= h($d['carnet']) ?></td>
              <td><?= h($d['grado'] . ' ' . $d['seccion']) ?></td>
              <td style="text-align:right;">$<?= number_format((float)$d['monto'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <div class="recibo-total">Total pagado: $<?= number_format((float)$pago['total'], 2) ?></div>

        <?php if ($pago['estado'] === 'Anulado'): ?>
          <div style="margin-top:16px; font-size:12.5px; color:#c9372c;">
            Anulado el <?= h(date('d/m/Y H:i', strtotime($pago['anulado_en']))) ?>. Motivo: <?= h($pago['motivo_anulacion']) ?>
          </div>
        <?php endif; ?>

        <div class="recibo-pie">Este recibo es un comprobante generado por el sistema Portal de Notas.</div>
      </div>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
