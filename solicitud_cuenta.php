<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/config/conexion.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

$nombre    = trim($_POST['nombre'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$carnet    = trim($_POST['carnet'] ?? '');
$estudiante = trim($_POST['estudiante'] ?? '');

if ($nombre === '' || $correo === '' || $estudiante === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Completa nombre, correo y nombre del estudiante.']);
    exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'El correo electrónico no es válido.']);
    exit;
}

// Si el carnet coincide con un estudiante existente, se vincula automáticamente
$idEstudiante = null;
$gradoEstudiante = null;
if ($carnet !== '') {
    $stmt = $pdo->prepare('SELECT id, grado FROM estudiantes WHERE carnet = ?');
    $stmt->execute([$carnet]);
    $fila = $stmt->fetch();
    if ($fila) {
        $idEstudiante = $fila['id'];
        $gradoEstudiante = $fila['grado'];
    }
}

$stmt = $pdo->prepare(
    'INSERT INTO solicitudes_cuenta (nombre_completo, correo, telefono, nombre_estudiante, grado_estudiante, id_estudiante, estado)
     VALUES (?,?,?,?,?,?,\'pendiente\')'
);
$stmt->execute([$nombre, $correo, $telefono ?: null, $estudiante, $gradoEstudiante, $idEstudiante]);

echo json_encode(['ok' => true, 'mensaje' => 'Solicitud enviada. La secretaría académica la revisará pronto.']);
